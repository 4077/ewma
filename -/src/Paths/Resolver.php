<?php namespace ewma\Paths;

use ewma\App\App;
use ewma\Service\Service;

class Resolver extends Service
{
    protected $services = ['app', 'paths'];

    /**
     * @var App
     */
    public $app = App::class;

    /**
     * @var Paths
     */
    public $paths = Paths::class;

    protected function boot()
    {
        $this->app->events->bind('app/terminate', function () {
            if ($this->saveOnAppTerminate && $this->cacheUpdated) {
                $this->saveCache();
            }
        });

        $this->loadCache();
    }

    private $saveOnAppTerminate = true;

    public function noSave()
    {
        $this->saveOnAppTerminate = false;
    }

    private $cache = [];

    private $cacheUpdated = false;

    private function loadCache()
    {
        $this->cache = $this->app->cache->read('pathResolver');

        if (is_scalar($this->cache)) {
            $report = [
                'test'        => 'read',
                'server'      => $_SERVER,
                'request'     => $_REQUEST,
                'session_key' => $this->app->session->getKey(),
                'value'       => $this->cache
            ];

            mdir('../tmp/pathResolverScalarBug');
            $f = fopen('../tmp/pathResolverScalarBug/' . date('Ymd-His') . '-read', 'a+');
            fwrite($f, print_r($report, true));
            fclose($f);

//            $this->sendSms('on read');

            $this->cache = [];
        }
    }

    private function saveCache()
    {
        $export = var_export(_j(j_($this->cache)), true);

        if (is_scalar($this->cache) || is_bool($export)) {
            $report = [
                'test'        => 'write',
                'server'      => $_SERVER,
                'request'     => $_REQUEST,
                'session_key' => $this->app->session->getKey(),
                'value'       => $this->cache,
                'export'      => $export
            ];

            mdir('../tmp/pathResolverScalarBug');
            $f = fopen('../tmp/pathResolverScalarBug/' . date('Ymd-His') . '-write', 'a+');
            fwrite($f, print_r($report, true));
            fclose($f);

            $this->sendSms('on save');
        }

        $this->app->cache->write('pathResolver', $this->cache);
    }

    public function sendSms($msg)
    {
        $ch = curl_init('https://sms.ru/sms/send');

        $data = [
            'api_id' => 'D3E13884-0B18-5BFD-8E46-E80A1667B7E4',
            'to'     => '+79099242827',
            'text'   => $msg,
            'from'   => false
        ];

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $body = curl_exec($ch);

        curl_close($ch);

        return $body;
    }

    /**
     * Преобразование пути $path в абсолютный относительно $basePath
     *
     * @param $path
     * @param $basePath
     *
     * @return string
     * @throws \Exception
     */
    public function resolve($path, $basePath)
    {
        if (!isset($this->cache[(string)$basePath][(string)$path])) {
            if (substr($path, 0, 1) == '#') {
                $resolved = $path;
            } else {
                $pathOriginal = $path;

                $path = $this->replaceShorts($path);
                $path = $this->replaceTildes($path);

                $firstSymbol = substr($path, 0, 1);

                if ($path) {
                    if ('\\' == $firstSymbol || '/' == $firstSymbol) {
                        $resolved = $this->resolveAbs($path, $firstSymbol, $pathOriginal);
                    } elseif ('^' == $firstSymbol) {
                        $resolved = $this->resolveRelativeToMaster($path, $basePath, $pathOriginal);
                    } elseif ('@' == $firstSymbol) {
                        $resolved = $this->resolveRelativeToParent($path, $basePath, $pathOriginal);
                    } elseif ('<' == $firstSymbol) {
                        $resolved = $this->resolveRelativeToSomeParent($path, $basePath, $pathOriginal);
                    } elseif ('>' == $firstSymbol) {
                        $resolved = $this->resolveNestedNode($path, $basePath, $pathOriginal);
                    } else {
                        $resolved = $this->resolveRelativeToModule($path, $basePath, $pathOriginal);
                    }
                } else {
                    $resolved = $basePath;
                }

                $resolved = force_l_slash($resolved);
            }

            $this->cache[(string)$basePath][(string)$path] = $resolved;
            $this->cacheUpdated = true;
        }

        return $this->cache[(string)$basePath][(string)$path];
    }

    /**
     * Замена сокращений
     *
     * @param $path
     *
     * @return string
     */
    public function replaceShorts($path)
    {
        if ($path == '^') {
            $path = '^ main';
        }

        if ($path == '>') {
            $path = '>main';
        }

        if ($path == '@') {
            $path = '@main';
        }

        return $path;
    }

    /**
     * Замена ~ на main
     *
     * Все тильды заменяются на main. Если путь не содержит пробел, то
     * он будет добавлен перед первым main, полученным путем замены.
     *
     * @param $path
     *
     * @return mixed|string
     * @throws \Exception
     */
    public function replaceTildes($path)
    {
        $pathOriginal = $path;

        $firstTildePos = strpos($path, '~');
        $hasTildes = false !== $firstTildePos;

        if (!$hasTildes) {
            return $path;
        }

        $spacePos = strpos($path, ' ');
        $hasSpace = false !== $spacePos;

        if ($hasSpace) {
            // путь не может содержать больше одного пробела
            if (is_numeric(strpos($path, ' ', $spacePos + 1))) {
                throw new \Exception('Path \'' . $pathOriginal . '\' contain more than one space');
            }

            // пробел не может располагаться правее первой тильды
            if ($spacePos > $firstTildePos) {
                throw new \Exception('Path \'' . $pathOriginal . '\' contain space after \'~\'');
            }
        }

        // 1. добавляем слэш после каждой тильды если его нет и если после нее что-то есть
        $path = preg_replace('/~([^\/]{1})/', '~/$1', $path);

        // 2. добавляем слэш перед каждой тильдой если его нет
        $path = preg_replace('/([^\/]{1})~/', '$1/~', $path);

        // 3. добавляем пробел перед первой тильдой если путь не содержит пробела изначально
        if (!$hasSpace) {
            $path = preg_replace('/(\S{1})~/', '$1 ~', $path, 1);
        }

        // 4. убираем ненужные слэши после пробелов, которые могли получиться в результате предыдущих замен
        $path = str_replace(' /', ' ', $path);

        // 5. убираем ненужный пробел для пути к узлу корневого модуля
        if (0 === strpos($path, '/ ')) {
            $path = '/' . substr($path, 2);
        }

        if (0 === strpos($path, '\ ')) {
            $path = '\\' . substr($path, 2);
        }

        // 6. убираем ненужные слэши перед пробелами
        $path = str_replace('/ ', ' ', $path);
        $path = str_replace('\ ', ' ', $path);

        // 7. превращаем тильду в main
        $path = str_replace('~', 'main', $path);

        return $path;
    }

    public function resolveAbs($path, $firstSymbol, $pathOriginal)
    {
        if ('\\' == $firstSymbol) {
            if ($pathInfo = $this->matchAbsByModuleNamespace($path)) {
                list($moduleNamespace, $nodePath) = $pathInfo;

                if (null !== $module = $this->app->modules->getByNamespace($moduleNamespace)) {
                    $modulePath = $module->path;

                    return $modulePath . ' ' . $nodePath;
                } else {
                    throw new \Exception('Not found module by path ' . $pathOriginal);
                }
            } elseif ($pathInfo = $this->matchAbsByModuleNamespaceAndPath($path)) {
                list($moduleNamespace, $modulePath, $nodePath) = $pathInfo;

                if (null !== $module = $this->app->modules->getByNamespace($moduleNamespace)) {
                    $modulePathByNamespace = $module->path;
                    $modulePathByNamespaceAndPath = $modulePathByNamespace . '/' . $modulePath;

                    return $modulePathByNamespaceAndPath . ' ' . $nodePath;
                } else {
                    throw new \Exception('Not found module by path ' . $pathOriginal);
                }
            } else {
                throw new \Exception('Has no match path ' . $pathOriginal);
            }
        }

        if ('/' == $firstSymbol) {
            if ($pathInfo = $this->matchAbsByModulePath($path)) {
                list($modulePath, $nodePath) = $pathInfo;

                return $modulePath . ' ' . $nodePath;
            } elseif ($pathInfo = $this->matchAbsNodePath($path)) {
                list($nodePath) = $pathInfo;

                return ' ' . $nodePath;
            } else {
                throw new \Exception('Has no match path ' . $pathOriginal);
            }
        }
    }

    public function resolveRelativeToMaster($path, $basePath, $pathOriginal)
    {
//        list($baseModulePath,) = $this->app->paths->separateAbsPath($basePath);

        if ($pathInfo = $this->matchFullPathRelativeToMasterModule($path)) {
            list($relativeModulePath, $nodePath) = $pathInfo;

            $masterModulePath = $this->paths->getMasterModuleAbsPath($basePath);

            return path($masterModulePath, $relativeModulePath) . ' ' . $nodePath;
        } elseif ($pathInfo = $this->matchNodePathRelativeToMasterModule($path)) {
            list($nodePath) = $pathInfo;

            $masterModulePath = $this->paths->getMasterModuleAbsPath($basePath);

            return $masterModulePath . ' ' . $nodePath;
        } else {
            throw new \Exception('Has no match path ' . $pathOriginal);
        }
    }

    public function resolveRelativeToParent($path, $basePath, $pathOriginal)
    {
        list($baseModulePath, $baseNodePath) = $this->app->paths->separateAbsPath($basePath);

        if ($pathInfo = $this->matchFullPathRelativeToParentModule($path)) {
            list($relativeModulePath, $nodePath) = $pathInfo;

            return path(path_slice($baseModulePath, 0, -1), $relativeModulePath) . ' ' . $nodePath;
        } elseif ($pathInfo = $this->matchNodePathRelativeToParentNode($path)) {
            list($nodePath) = $pathInfo;

            return $baseModulePath . ' ' . path(path_slice($baseNodePath, 0, -1), $nodePath);
        } else {
            throw new \Exception('Has no match path ' . $pathOriginal);
        }
    }

    public function resolveRelativeToModule($path, $basePath, $pathOriginal)
    {
        list($baseModulePath,) = $this->app->paths->separateAbsPath($basePath);

        if ($pathInfo = $this->matchFullPathRelativeToModule($path)) {
            list($relativeModulePath, $nodePath) = $pathInfo;

            return path($baseModulePath, $relativeModulePath) . ' ' . $nodePath;
        } elseif ($pathInfo = $this->matchNodePathRelativeToModule($path)) {
            list($nodePath) = $pathInfo;

            return $baseModulePath . ' ' . $nodePath;
        } else {
            throw new \Exception('Has no match path ' . $pathOriginal);
        }
    }

    public function resolveRelativeToSomeParent($path, $basePath, $pathOriginal)
    {
        list($baseModulePath, $baseNodePath) = $this->app->paths->separateAbsPath($basePath);

        if ($pathInfo = $this->matchFullPathRelativeToSomeParentModule($path)) {
            list($parentOffset, $relativeModulePath, $nodePath) = $pathInfo;
            $parentOffsetLevel = strlen($parentOffset);

            return path(path_slice($baseModulePath, 0, -$parentOffsetLevel), $relativeModulePath) . ' ' . $nodePath;
        } elseif ($pathInfo = $this->matchNodePathRelativeToSomeParentNode($path)) {
            list($parentOffset, $nodePath) = $pathInfo;
            $parentOffsetLevel = strlen($parentOffset);

            return $baseModulePath . ' ' . path(path_slice($baseNodePath, 0, -$parentOffsetLevel), $nodePath);
        } else {
            throw new \Exception('Has no match path ' . $pathOriginal);
        }
    }

    public function resolveNestedNode($path, $basePath, $pathOriginal)
    {
        list($baseModulePath, $baseNodePath) = $this->app->paths->separateAbsPath($basePath);

        if ($pathInfo = $this->matchNestedNodePath($path)) {
            list($nodePath) = $pathInfo;

            return $baseModulePath . ' ' . path($baseNodePath, $nodePath);
        } else {
            throw new \Exception('Has no match path ' . $pathOriginal);
        }
    }

    /**
     * Проверка на абсолютный путь, состоящий из нейспейса модуля и путя к узлу
     *
     * \abc main
     * \abc\def main/view
     *
     * @param $path
     *
     * @return array
     */
    public function matchAbsByModuleNamespace($path)
    {
        if (preg_match('/^\\\\(?<moduleNamespace>[\w\\\\]*)\s(?<nodePath>[\w.\-\/]+)$/', $path, $matches)) {
            return [$matches['moduleNamespace'], $matches['nodePath']];
        }
    }

    /**
     * Проверка на абсолютный путь, состоящий из пути к модулю и пути к узлу
     *
     * /abc main
     * /abc/def main/view
     *
     * @param $path
     *
     * @return array
     */
    public function matchAbsByModulePath($path)
    {
        if (preg_match('/^\/(?<modulePath>[\w.\-\/]*)\s(?<nodePath>[\w.\-\/]+)$/', $path, $matches)) {
            return [$matches['modulePath'], $matches['nodePath']];
        }
    }

    /**
     * Проверка на абсолютный путь к узлу корневого модуля
     *
     * /main
     * /main/view
     *
     * @param $path
     *
     * @return array
     */
    public function matchAbsNodePath($path)
    {
        if (preg_match('/^\/(?<nodePath>[\w.\-\/]+)$/', $path, $matches)) {
            return [$matches['nodePath']];
        }
    }

    /**
     * Проверка на абсолютный путь, состоящий из комбинированного пути к модулю и пути к узлу
     *
     * \abc/def main
     * \abc\def/xyz/pqr main/view
     *
     * @param $path
     *
     * @return array
     */
    public function matchAbsByModuleNamespaceAndPath($path)
    {
        if (preg_match('/^\\\\(?<moduleNamespace>[\w\\\\]*)\/(?<modulePath>[\w\/]+)\s(?<nodePath>[\w.\-\/]+)$/', $path, $matches)) {
            return [$matches['moduleNamespace'], $matches['modulePath'], $matches['nodePath']];
        }
    }

    /**
     * Проверка на путь относительный корня мастер-модуля, состоящий из пути к модулю и пути к узлу
     *
     * ^abc main
     * ^abc/def main/view
     *
     * @param $path
     *
     * @return array
     */
    public function matchFullPathRelativeToMasterModule($path)
    {
        if (preg_match('/^\^(?<modulePath>[\w\/]+)?\s(?<nodePath>[\w.\-\/]+)$/', $path, $matches)) {
            return [$matches['modulePath'], $matches['nodePath']];
        }
    }

    /**
     * Проверка на путь к узлу мастер-модуля
     *
     * ^abc
     * ^abc/def
     *
     * @param $path
     *
     * @return array
     */
    public function matchNodePathRelativeToMasterModule($path)
    {
        if (preg_match('/^\^(?<nodePath>[\w.\-\/]+)$/', $path, $matches)) {
            return [$matches['nodePath']];
        }
    }

    /**
     * Проверка на относительный корня текущего модуля путь, состоящий из пути к модулю и пути к узлу
     *
     * abc main
     * abc/def main/view
     *
     * @param $path
     *
     * @return array
     */
    public function matchFullPathRelativeToModule($path)
    {
        if (preg_match('/^(?<modulePath>[\w\/]+)\s(?<nodePath>[\w.\-\/]+)$/', $path, $matches)) {
            return [$matches['modulePath'], $matches['nodePath']];
        }
    }

    /**
     * Проверка на относительный корня текущего модуля путь к узлу
     *
     * abc
     * abc/def
     *
     * @param $path
     *
     * @return array
     */
    public function matchNodePathRelativeToModule($path)
    {
        if (preg_match('/^(?<nodePath>[\w.\-\/]+)$/', $path, $matches)) {
            return [$matches['nodePath']];
        }
    }

    /**
     * Проверка на относительный корня родительского модуля путь, состоящий из пути к модулю и пути к узлу
     *
     * @abc main
     * @abc /def main/view
     *
     * @param $path
     *
     * @return array
     */
    public function matchFullPathRelativeToParentModule($path)
    {
        if (preg_match('/^@(?<modulePath>[\w\/]+)\s(?<nodePath>[\w.\-\/]+)$/', $path, $matches)) {
            return [$matches['modulePath'], $matches['nodePath']];
        }
    }

    /**
     * Проверка на относительный корня родительского модуля путь к узлу
     *
     * @abc
     * @abc/def
     *
     * @param $path
     *
     * @return array
     */
    public function matchNodePathRelativeToParentNode($path)
    {
        if (preg_match('/^@(?<nodePath>[\w.\-\/]+)$/', $path, $matches)) {
            return [$matches['nodePath']];
        }
    }

    /**
     * Проверка на путь относительный одного из родительских модулей, состоящий из пути к модулю и пути к узлу
     *
     * <abc main
     * <<abc/def main/view
     *
     * @param $path
     *
     * @return array
     */
    public function matchFullPathRelativeToSomeParentModule($path)
    {
        if (preg_match('/^(?<parentOffset><+)(?<modulePath>[\w\/]+)?\s(?<nodePath>[\w.\-\/]+)$/', $path, $matches)) {
            $modulePath = isset($matches['modulePath']) ? $matches['modulePath'] : '';

            return [$matches['parentOffset'], $modulePath, $matches['nodePath']];
        }
    }

    /**
     * Проверка на путь к узлу одного из родительских модулей
     *
     * <abc
     * <<abc/def
     *
     * @param $path
     *
     * @return array
     */
    public function matchNodePathRelativeToSomeParentNode($path)
    {
        if (preg_match('/^(?<parentOffset><+)(?<nodePath>[\w.\-\/]+)?$/', $path, $matches)) {
            $nodePath = isset($matches['nodePath']) ? $matches['nodePath'] : '';

            return [$matches['parentOffset'], $nodePath];
        }
    }

    /**
     * Проверка на путь к вложенному узлу
     *
     * >abc
     * >abc/def
     *
     * @param $path
     *
     * @return array
     */
    public function matchNestedNodePath($path)
    {
        if (preg_match('/^>(?<nodePath>[\w.\-\/]+)$/', $path, $matches)) {
            return [$matches['nodePath']];
        }
    }
}
