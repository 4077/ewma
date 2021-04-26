<?php namespace ewma\Data;

class Modifiers
{
    /**
     * Получение значения узла или ссылки на узел с возможностью одновременного изменения значения этого узла
     *
     * @param      $array
     * @param null $path
     * @param null $setValue
     *
     * @return null
     */
    public static function &arrayNodeAccess(&$array, $path = null, $setValue = null)
    {
        if (null !== $path) {
            if (is_array($path)) {
                $pathArray = $path;
            } else {
                $pathArray = Converters::pathToArray($path);
            }

            $node = &$array;

            if (null !== $setValue) {
                foreach ($pathArray as $segment) {
                    if (!isset($node[$segment]) || !is_array($node[$segment])) {
                        $node[$segment] = false;
                    }

                    $node = &$node[$segment];
                }

                $node = $setValue;

                return $node;
            } else {
                foreach ($pathArray as $segment) {
                    if (!isset($node[$segment])) {
                        if (!is_array($node)) { // php 7.1 fix
                            $node = [];
                        }

                        $node[$segment] = null;
                    }

                    $node = &$node[$segment];
                }

                return $node;
            }
        } else {
            return $array;
        }
    }

    /**
     * Обновление узлов массива $target узлами массива $source.
     * Если в $target узел отсутствует, то будет создан.
     *
     * @param           $target
     * @param array     $source
     * @param bool|true $rewrite
     */
    private static function arrayUpdate(&$target, $source, $rewrite = true)
    {
        $flat = Converters::arrayToFlat($source);

        if ($rewrite) {
            foreach ($flat as $path => $value) {
                self::arrayNodeAccess($target, $path, $value);
            }
        } else {
            foreach ($flat as $path => $value) {
                if (null === self::arrayNodeAccess($target, $path)) {
                    self::arrayNodeAccess($target, $path, $value);
                }
            }
        }
    }

    /**
     * Добавление в массив $target узлов из $source.
     * Уже существующие узлы не будут изменены.
     *
     * @param $target
     * @param $source
     */
    public static function addToArray(&$target, $source)
    {
        self::arrayUpdate($target, $source, false);
    }

    /**
     * Добавление и обновление узов в массиве $target из массива $source.
     *
     * @param $target
     * @param $source
     */
    public static function rewriteToArray(&$target, $source)
    {
        self::arrayUpdate($target, $source, true);
    }

    /**
     * @param       $source
     * @param       $target
     * @param array $mappingsList
     */
    public static function remap(&$target, &$source, $mappingsList = [], $flipMappings = false)
    {
        if ($mappingsList == '*') {
            $mappingsList = [];

            merge($mappingsList, array_keys(a2f($target)));
            merge($mappingsList, array_keys(a2f($source)));
        }

        if ($mappingsList) {
            $mappings = Converters::listToArray($mappingsList);

            foreach ($mappings as $mapping) {
                if (false !== strpos($mapping, '  ')) {
                    $mapping = preg_replace('/\s{2,}/', ' ', $mapping);
                }

                $mappingPaths = explode(' ', $mapping);

                if ($flipMappings) {
                    $sourcePath = $mappingPaths[0];
                    $targetPath = isset($mappingPaths[1]) ? $mappingPaths[1] : $mappingPaths[0];
                } else {
                    $sourcePath = isset($mappingPaths[1]) ? $mappingPaths[1] : $mappingPaths[0];
                    $targetPath = $mappingPaths[0];
                }

                $sourceValue = self::arrayNodeAccess($source, $sourcePath);

                if (null !== $sourceValue) {
                    self::arrayNodeAccess($target, $targetPath, $sourceValue);
                }
            }
        }
    }
}