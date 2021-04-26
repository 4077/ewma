<?php namespace ewma\Data\Table;

class Transformer
{
    public static function getRowsByField($rows, $field)
    {
        $rows = $rows ?: [];

        $output = [];
        foreach ($rows as $row) {
            $output[$row->$field] = $row;
        }

        return $output;
    }

    public static function getRowsById($rows)
    {
        return self::getRowsByField($rows, 'id');
    }

    public static function getColumn($rows, $field)
    {
        $rows = $rows ?: [];

        $output = [];
        foreach ($rows as $n => $row) {
            $output[$n] = $row->$field;
        }

        return $output;
    }

    public static function getColumns($rows, $fieldsList = []) // models only (not stdObject)
    {
        $rows = $rows ?: [];

        $fields = \ewma\Data\Converters::listToArray($fieldsList);

        $output = [];
        foreach ($rows as $n => $row) {
            foreach ($row as $fieldName => $value) {
                if (!$fields || in_array($fieldName, $fields)) {
                    $output[$fieldName][$n] = $value;
                }
            }
        }

        return $output;
    }

    public static function getCellOfFirstRow($rows, $fieldName)
    {
        if ($rows) {
            return $rows[0]->$fieldName;
        }
    }

    public static function getCellsByField($rows, $indexFieldName, $valueFieldName)
    {
        $rows = $rows ?: [];

        $output = [];
        foreach ($rows as $row) {
            $output[$row->$indexFieldName] = $row->$valueFieldName;
        }

        return $output;
    }

    public static function getCellsById($rows, $valueFieldName)
    {
        return self::getCellsByField($rows, 'id', $valueFieldName);
    }

    public static function getCells($rows, $valueFieldName)
    {
        return array_values(self::getCellsByField($rows, 'id', $valueFieldName));
    }

    public static function getIds($rows)
    {
        return array_values(self::getColumn($rows, 'id'));
    }
}
