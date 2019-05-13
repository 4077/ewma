<?php namespace ewma\Paths;

use ewma\App\App;
use ewma\Service\Service;
use ewma\Controllers\Controller;

class Paths extends Service
{
    protected $services = ['app', 'resolver'];

    /**
     * @var App
     */
    public $app = App::class;

    /**
     * @var Resolver
     */
    public $resolver = Resolver::class;

    /**
     * @param $path
     * @param $basePath
     *
     * @return string
     */
    public function resolve($path, $basePath)
    {
        return $this->resolver->resolve($path, $basePath);
    }

    /**
     * Поиск пути ближайшего мастер-модуля
     *
     * @param $basePath
     *
     * @return bool
     * @throws \Exception
     */
    public function getMasterModuleAbsPath($basePath)
    {
        list($modulePath,) = $this->separateAbsPath($basePath);

        $module = $this->app->modules->getByPath($modulePath);

        if ($module) {
            return $module->masterModulePath;
        } else {
            throw new \Exception('Not found module ' . $basePath);
        }
    }

    /**
     * Путь к файлу узла
     *
     * @param            $path
     * @param            $nodeType
     * @param Controller $controller
     *
     * @return mixed
     */
    public function getNodeFilePath($path, $nodeType, Controller $controller)
    {
        $absPath = $this->resolve($path, $controller->__meta__->absPath);

        if (!isset($this->resolvedNodesFilesPaths[$controller->__meta__->absPath][(string)$path][$nodeType])) {
            list($modulePath, $nodePath) = $this->separateAbsPath($absPath);

            $node = $controller->n($path);

            $modulesDir = $node->__meta__->module->location == 'local'
                ? 'modules'
                : 'modules-vendor/' . $node->__meta__->module->vendor;

            $moduleNodesDirPath = $modulePath ? '/' . $modulesDir . '/' . $modulePath . '/-' : '';
            $nodeFilePath = ($nodeType ? '/' . $nodeType : '') . '/' . $nodePath;

            $this->resolvedNodesFilesPaths[$controller->__meta__->absPath][(string)$path][$nodeType] = path($moduleNodesDirPath, $nodeFilePath);
        }

        return $this->resolvedNodesFilesPaths[$controller->__meta__->absPath][(string)$path][$nodeType];
    }

    private $resolvedNodesFilesPaths;

    /**
     * Разделение на абсолютный путь к модулю и путь к узлу внутри модуля
     *
     * @param $absPath
     *
     * @return array
     */
    public function separateAbsPath($absPath)
    {
        $separated = explode(' ', $absPath);
        $modulePath = isset($separated[1]) ? $separated[0] : '';
        $nodePath = isset($separated[1]) ? $separated[1] : $separated[0];

        return [trim_slashes($modulePath), trim_slashes($nodePath)];
    }

    /**
     * Поиск относительного пути для двух абсолютных
     *
     * Слегка позаимствовано https://github.com/symfony/routing/blob/master/Generator/UrlGenerator.php
     *
     * @param $targetPath
     * @param $basePath
     *
     * @return string
     */
    public function getRelativePath($targetPath, $basePath)
    {
        if ($basePath === $targetPath) {
            return '';
        }

        $basePathArray = explode('/', $basePath);
        $targetPathArray = explode('/', $targetPath);

        array_pop($basePathArray);
        $targetNode = array_pop($targetPathArray);

        foreach ($basePathArray as $n => $basePathSegment) {
            if (isset($targetPathArray[$n]) && $basePathSegment === $targetPathArray[$n]) {
                unset($basePathArray[$n], $targetPathArray[$n]);
            } else {
                break;
            }
        }

        $targetPathArray[] = $targetNode;

        return str_repeat('../', count($basePathArray)) . implode('/', $targetPathArray);
    }

    /**
     * http://stackoverflow.com/questions/20522605/what-is-the-best-way-to-resolve-a-relative-path-like-realpath-for-non-existing
     * Позаимствовано https://github.com/thephpleague/flysystem/blob/master/src/Util.php#L80
     *
     * Normalize path
     *
     * @param   string $path
     *
     * @return  string normalized path
     */
    public function normalizePath($path)
    {
        // Remove any kind of funky unicode whitespace
        $normalized = preg_replace('#\p{C}+|^\./#u', '', $path);

        $normalized = $this->normalizeRelativePath($normalized);

        if (preg_match('#/\.{2}|^\.{2}/|^\.{2}$#', $normalized)) {
            throw new \LogicException(
                'Path is outside of the defined root, path: [' . $path . '], resolved: [' . $normalized . ']'
            );
        }

        $normalized = preg_replace('#\\\{2,}#', '\\', $normalized);
        $normalized = preg_replace('#/{2,}#', '/', $normalized);

        return $normalized;
    }

    /**
     * Позаимствовано https://github.com/thephpleague/flysystem/blob/master/src/Util.php#L80
     *
     * Normalize relative directories in a path.
     *
     * @param string $path
     *
     * @return string
     */
    public function normalizeRelativePath($path)
    {
        // Path remove self referring paths ("/./").
        $path = preg_replace('#/\.(?=/)|^\./|/\./?$#', '', $path);

        // Regex for resolving relative paths
        $regex = '#/*[^/\.]+/\.\.#Uu';

        while (preg_match($regex, $path)) {
            $path = preg_replace($regex, '', $path);
        }

        return $path;
    }

    public function getFingerprintPath($fingerprint, $fingerprint2 = null)
    {
        $dirPath = implode('/', str_split(substr($fingerprint, 0, 8), 2));

        if (null !== $fingerprint2) {
            $fileName = substr($fingerprint2, 0, 8);
        } else {
            $fileName = substr($fingerprint, 7, 8);
        }

        return $dirPath . '/' . $fileName;
    }
}
