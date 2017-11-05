<?php namespace Ewma;

/**
 * Порядок поиска:
 *
 * 1. Если полное имя класса содержит сегмент models, то:
 *      все, что слева от этого сегмента, считается неймспейсом модуля
 *      все, что справа - путем по папкам внутри папки models модуля с этим неймспейсом
 *
 * 2. Если полное имя класса содержит сегмент controllers, то:
 *      все, что слева от этого сегмента, считается неймспейсом модуля
 *      все, что справа - путем по папкам внутри папки controllers модуля с этим неймспейсом
 *
 * 3. Производится поиск модуля с неймспейом совпадающим с началом полного имени класса
 *      оставшаяся часть считается путем по папкам внутри папки src найденного модуля
 *
 * 4. Производится поиск класса в папке src корневого модуля
 *
 * 5. Автозагрузка композера
 */

class Autoload
{
    private static $appRoot;

    public static function init($appRoot)
    {
        static::$appRoot = $appRoot;

        static::loadCache();
        static::registerModule('ewma', 'fed/ewma', 'vendor');
    }

    private static $cache = [];

    public static $cacheUpdated = false;

    public static function loadCache()
    {
        static::$cache = aread(static::$appRoot . '/cache/autoload.php');
    }

    public static function saveCache()
    {
        $cachePath = static::$appRoot . '/cache/autoload.php';

        awrite($cachePath, static::$cache);
    }

    /**
     * Кеш путей модулей по неймспейсам
     *
     * @var array
     */
    private static $modulesPathsByNamespaces = [];

    /**
     * Кеш типов расположения модулей по неймспейсам
     *
     * @var array
     */
    private static $modulesLocationsByNamespaces = [];

    /**
     * Кеш путей внешних модулей
     *
     * @var array
     */
    private static $externalModulesPaths = [];

    public static function registerModule($namespace, $path, $location = null, $externalPath = null)
    {
        if (!in_array($path, static::$modulesPathsByNamespaces)) {
            static::$modulesPathsByNamespaces[$namespace] = $path;
            static::$modulesLocationsByNamespaces[$namespace] = $location;
            static::$externalModulesPaths[$namespace] = $externalPath;
        }
    }

    public static function load($class)
    {
        if (isset(static::$cache[$class])) {
            if (static::$cache[$class] == false) {
                return null;
            } else {
                include static::$cache[$class];

                return true;
            }
        }

        if (!isset(static::$cache[$class])) {
            $classPathArray = explode('\\', $class);

            // поиск модели
            if (in_array('models', $classPathArray)) {
                $pos = array_search('models', $classPathArray);

                $moduleNamespace = implode('\\', array_slice($classPathArray, 0, $pos));

                $moduleDirPath = static::getModuleDirPath($moduleNamespace);

                $filePath = $moduleDirPath . '/-/models/' . implode('/', array_slice($classPathArray, $pos + 1)) . '.php';

                if (file_exists($filePath)) {
                    $found = true;
                }
            }

            // поиск контроллера
            if (in_array('controllers', $classPathArray)) {
                $pos = array_search('controllers', $classPathArray);

                $moduleNamespace = implode('\\', array_slice($classPathArray, 0, $pos));

                $moduleDirPath = static::getModuleDirPath($moduleNamespace);

                $classPathArray[count($classPathArray) - 1] = lcfirst(end($classPathArray));

                $filePath = $moduleDirPath . '/-/controllers/' . implode('/', array_slice($classPathArray, $pos + 1)) . '.php';

                if (file_exists($filePath)) {
                    $found = true;
                }
            }

            // поиск в src
            if (empty($found)) {
                $lastFoundModuleNamespace = '';
                $searchNamespace = '';

                $classPathTailArray = $classPathArray;

                for ($i = 0; $i < count($classPathArray) - 1; $i++) {
                    $searchNamespace .= '/' . $classPathArray[$i];
                    $moduleNamespace = str_replace('/', '\\', trim_l_slash($searchNamespace));

                    if (isset(static::$modulesPathsByNamespaces[$moduleNamespace])) {
                        $lastFoundModuleNamespace = $moduleNamespace;

                        //$lastFoundModulePath = static::$modulesPathsByNamespaces[$moduleNamespace];

                        array_shift($classPathTailArray);
                    }
                }

                $moduleDirPath = static::getModuleDirPath($lastFoundModuleNamespace); // '/' . path(static::$appRoot . '/modules', $lastFoundModulePath);

                $filePath = $moduleDirPath . '/-/src/' . implode('/', $classPathTailArray) . '.php';

                if (file_exists($filePath)) {
                    $found = true;
                }
            }

            if (!empty($found)) {
                static::$cache[$class] = $filePath;

                include $filePath;
            } else {
                static::$cache[$class] = false;
            }

            static::$cacheUpdated = true;
        }
    }

    private static function getModuleDirPath($moduleNamespace)
    {
        $location = static::$modulesLocationsByNamespaces[$moduleNamespace];

        if ($location == 'local') {
            $moduleDirPath = '/' . path(static::$appRoot . '/modules', static::$modulesPathsByNamespaces[$moduleNamespace]);
        } elseif ($location == 'vendor') {
            $moduleDirPath = '/' . path(static::$appRoot . '/modules-vendor', static::$modulesPathsByNamespaces[$moduleNamespace]);
        } else {
            $moduleDirPath = path(static::$externalModulesPaths[$moduleNamespace], static::$modulesPathsByNamespaces[$moduleNamespace]);
        }

        return $moduleDirPath;
    }
}
