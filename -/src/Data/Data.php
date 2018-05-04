<?php namespace ewma\Data;

class Data
{
    public static function extract($target, $source, $mappingsList = [])
    {
        if ($mappingsList) {
            $mappings = Converters::listToArray($mappingsList);

            foreach ($mappings as $mapping) {
                if (false !== strpos($mapping, '  ')) {
                    $mapping = preg_replace('/\s{2,}/', ' ', $mapping);
                }

                $mappingPaths = explode(' ', $mapping);

                $sourcePath = isset($mappingPaths[1]) ? $mappingPaths[1] : $mappingPaths[0];
                $targetField = $mappingPaths[0];

                $sourceValue = Modifiers::arrayNodeAccess($source, $sourcePath);

                $target->{$targetField} = $sourceValue;
            }
        }
    }

    public static function compact(&$target, $source, $mappingsList = [])
    {
        if ($mappingsList) {
            $mappings = Converters::listToArray($mappingsList);

            foreach ($mappings as $mapping) {
                if (false !== strpos($mapping, '  ')) {
                    $mapping = preg_replace('/\s{2,}/', ' ', $mapping);
                }

                $mappingPaths = explode(' ', $mapping);

                $sourceField = isset($mappingPaths[1]) ? $mappingPaths[1] : $mappingPaths[0];
                $targetPath = $mappingPaths[0];

                $sourceValue = isset($source->{$sourceField}) ? $source->{$sourceField} : null;

                Modifiers::arrayNodeAccess($target, $targetPath, $sourceValue);
            }
        }
    }

    public static function tokenize($input, $replacements = []) // похоже это был корявый костыль
    {
        if (is_array($input)) {
            $replacementsKeys = array_keys($replacements);
            $inputFlat = a2f($input);

            foreach ($inputFlat as $path => $value) {
                if (in_array($value, $replacementsKeys, true)) {
                    $inputFlat[$path] = $replacements[$value];
                }
            }

            return f2a($inputFlat);
        }

        if (is_string($input)) {
            $output = $input;

            foreach ($replacements as $key => $value) {
                if (is_scalar($value) || $output == '{' . $key . '}') { // замена на массивы только если шаблон замены не является частью строки
                    $output = preg_replace('/\{' . $key . '\}/U', $value, $output);
                }
            }

            return $output;
        }
    }
}
