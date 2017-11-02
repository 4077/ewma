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
        static::registerModule('ewma', 'fed/ewma');
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
     * Кеш путей модулей по их неймспейсам
     *
     * @var array
     */
    private static $modulesPathsByNamespaces = [];

    public static function registerModule($moduleNamespace, $modulePath)
    {
        if (!in_array($modulePath, static::$modulesPathsByNamespaces)) {
            static::$modulesPathsByNamespaces[$moduleNamespace] = $modulePath;
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

                $moduleDirPath = '/' . path(static::$appRoot . '/modules', static::$modulesPathsByNamespaces[$moduleNamespace]);

                $filePath = $moduleDirPath . '/-/models/' . implode('/', array_slice($classPathArray, $pos + 1)) . '.php';

                if (file_exists($filePath)) {
                    $found = true;
                }
            }

            // поиск контроллера
            if (in_array('controllers', $classPathArray)) {
                $pos = array_search('controllers', $classPathArray);

                $moduleNamespace = implode('\\', array_slice($classPathArray, 0, $pos));

                $moduleDirPath = '/' . path(static::$appRoot . '/modules', static::$modulesPathsByNamespaces[$moduleNamespace]);

                $classPathArray[count($classPathArray) - 1] = lcfirst(end($classPathArray));

                $filePath = $moduleDirPath . '/-/controllers/' . implode('/', array_slice($classPathArray, $pos + 1)) . '.php';

                if (file_exists($filePath)) {
                    $found = true;
                }
            }

            // поиск в src
            if (empty($found)) {
                $lastFoundModulePath = '';
                $searchNamespace = '';

                $classPathTailArray = $classPathArray;

                for ($i = 0; $i < count($classPathArray) - 1; $i++) {
                    $searchNamespace .= '/' . $classPathArray[$i];
                    $moduleNamespace = str_replace('/', '\\', trim_l_slash($searchNamespace));

                    if (isset(static::$modulesPathsByNamespaces[$moduleNamespace])) {
                        $lastFoundModulePath = static::$modulesPathsByNamespaces[$moduleNamespace];

                        array_shift($classPathTailArray);
                    }
                }

                $moduleDirPath = '/' . path(static::$appRoot . '/modules', $lastFoundModulePath);

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
}
