<?php namespace ewma\handlers;

use ewma\handlers\models\Assignment as AssignmentModel;

class Assignments
{
    public static $containersCombineModes = [
        'first'  => 'Будет использовано первое не null значение. Остальные значения обрабатываться не будут',
//        'link'   => 'Будет использована ссылка на первое не null значение. Остальные значения обрабатываться не будут',
        'concat' => 'Все вложенные значения скалярных типов будут объединены в строку. Значения других типов будут проигнорированы.',
        'add'    => 'Вложенные значения будут объединены с помощью оператора +. В объединении смогут участвовать либо значения скалярных типов, либо массивы. В зависимости от того, какой из этих типов встретится раньше. Значения других типов будут проигнорированы.',
        'array'  => 'Все вложенные значения будут собраны в массив',
        'aa'     => 'Принимаются только массивы, каждый следующий массив дополняет предыдущие несуществующими узлами.',
        'ra'     => 'Принимаются только массивы, каждый следующий массив дополняет предыдущие несуществующими узлами и перезаписывает существующие.'
    ];

    public static $valuesTypes = [
        'bool'       => 'Двоичное значение',
        'string'     => 'Строка',
        'array'      => 'Массив',
//        'expression' => 'Выражение'
    ];

    public static $assignmentsTypes = [
        'OUTPUT'  => 'Выход',
        'HANDLER' => 'Обработчик',
        'VALUE'   => 'Значение',
        'VAR'     => 'Переменная',
        'INPUT'   => 'Вход',
    ];

    public static function get($assignmentId)
    {
        return AssignmentModel::find($assignmentId);
    }

    public static function getOutput($assignmentId)
    {
        return AssignmentModel::where('type', 'OUTPUT')->where('id', $assignmentId)->first();
    }

    public static function getOutputByName($name)
    {
        return AssignmentModel::where('type', 'OUTPUT')->where('name', $name)->first();
    }

    public static function getHandler($assignmentId)
    {
        return AssignmentModel::where('type', 'HANDLER')->where('id', $assignmentId)->first();
    }

    public static function getValue($assignmentId)
    {
        return AssignmentModel::where('type', 'VALUE')->where('id', $assignmentId)->first();
    }

    public static function getContainer($assignmentId) // OUTPUT, VAR, INPUT
    {
        return AssignmentModel::where(function ($query) {
            $query->where('type', 'OUTPUT')->orWhere('type', 'INPUT')->orWhere('type', 'VAR');
        })->where('id', $assignmentId)->first();
    }

    public static function isContainer($assignment)
    {
        return in($assignment->type, 'OUTPUT, VAR, INPUT');
    }

    public static function getReturner($assignmentId) // HANDLER, VALUE
    {
        return AssignmentModel::where(function ($query) {
            $query->where('type', 'HANDLER')->orWhere('type', 'VALUE');
        })->where('id', $assignmentId)->first();
    }

    public static function isReturner($assignment)
    {
        return in($assignment->type, 'HANDLER, VALUE');
    }

    public static function createOutput()
    {
        return AssignmentModel::create([
                                           'type' => 'OUTPUT',
                                           'data' => j_([
                                                            'combine_mode' => 'concat'
                                                        ])
                                       ]);
    }

    public static function createHandler($targetAssignmentId = false)
    {
        $attributes = [
            'type' => 'HANDLER',
            'data' => json_encode([
                                      'name' => '',
                                      'path' => '',
                                      'desc' => ''
                                  ])
        ];

        if ($targetAssignmentId) {
            if ($targetAssignment = self::getContainer($targetAssignmentId)) {
                return $targetAssignment->nested()->create($attributes);
            }
        } else {
            return AssignmentModel::create($attributes);
        }
    }

    public static function createValue($targetAssignmentId = false)
    {
        $attributes = [
            'type' => 'VALUE',
            'data' => json_encode([
                                      'name'  => '',
                                      'type'  => 'string',
                                      'value' => [
                                          'string'     => '',
                                          'array'      => [],
                                          'bool'       => false,
                                          'expression' => ''
                                      ]
                                  ])
        ];

        if ($targetAssignmentId) {
            if ($targetAssignment = self::getContainer($targetAssignmentId)) {
                return $targetAssignment->nested()->create($attributes);
            }
        } else {
            return AssignmentModel::create($attributes);
        }
    }

    public static function createVar($targetAssignmentId)
    {
        if ($targetAssignment = self::getHandler($targetAssignmentId)) {
            return $targetAssignment->nested()->create([
                                                           'type' => 'VAR',
                                                           'data' => json_encode([
                                                                                     'name'         => '',
                                                                                     'path'         => '',
                                                                                     'combine_mode' => 'first'
                                                                                 ])
                                                       ]);
        }
    }

    public static function createInput($targetAssignmentId)
    {
        if ($targetAssignment = self::getHandler($targetAssignmentId)) {
            return $targetAssignment->nested()->create([
                                                           'type' => 'INPUT',
                                                           'data' => json_encode([
                                                                                     'name'         => '',
                                                                                     'path'         => '',
                                                                                     'desc'         => '',
                                                                                     'combine_mode' => 'first'
                                                                                 ])
                                                       ]);
        }
    }

    public static function setContainerCombineMode($assignmentId, $mode)
    {
        if (in($mode, array_keys(self::$containersCombineModes)) && $assignment = self::getContainer($assignmentId)) {
            $data = _j($assignment->data);

            ap($data, 'combine_mode', $mode);

            $assignment->data = j_($data);
            $assignment->save();

            return $assignment;
        }
    }

    public static function setValueType($assignmentId, $type)
    {
        if (in($type, array_keys(self::$valuesTypes)) && $assignment = self::getValue($assignmentId)) {
            $data = _j($assignment->data);

            ap($data, 'type', $type);

            $assignment->data = j_($data);
            $assignment->save();

            return $assignment;
        }
    }

    private static $deleted;

    public static function delete($assignmentId)
    {
        self::$deleted = [];

        if ($assignment = AssignmentModel::find($assignmentId)) {
            \DB::transaction(function () use ($assignment) {
                self::deleteRecursion($assignment);
            });
        }

        return self::$deleted;
    }

    private static function deleteRecursion($assignment)
    {
        $nested = $assignment->nested;
        foreach ($nested as $source) {
            self::deleteRecursion($source);
        }

        self::$deleted[] = $assignment->id;

        $assignment->delete();
    }

    private static $copyingMap = [
        'HANDLER' => 'OUTPUT, INPUT, VAR',
        'VALUE'   => 'OUTPUT, INPUT, VAR',
        'INPUT'   => 'HANDLER',
        'VAR'     => 'HANDLER'
    ];

    public static function canBePasted($sourceType, $targetType)
    {
        return in($targetType, self::$copyingMap[$sourceType]);
    }

    public static function paste($sourceId, $targetId)
    {
        if ($source = AssignmentModel::find($sourceId)) {
            $target = null;

            \DB::transaction(function () use ($source, $targetId, &$target) {
                $target = AssignmentModel::find($targetId);

                if ($target && self::canBePasted($source->type, $target->type)) {
                    $attributes = $source->toArray();
                    $attributes['parent_id'] = 0;

                    $new = AssignmentModel::create($attributes);

                    self::pasteRecursion($new, $source->nested()->orderBy('position')->get());

                    $new->parent_id = $target->id;
                    $new->save();
                }
            });

            return $target;
        }
    }

    private static function pasteRecursion($target, $nested)
    {
        foreach ($nested as $source) {
            $new = $target->nested()->create($source->toArray());

            self::pasteRecursion($new, $source->nested()->orderBy('position')->get());
        }
    }

    public static function saveAsGlobal($assignmentId, $catId)
    {
        $local = AssignmentModel
            ::where('source_id', 0)
            ->where('id', $assignmentId)
            ->first();

        if ($local) {
            $local = $local->used();

            \DB::transaction(function () use ($local, &$target, $catId) {
                $attributes = $local->toArray();

                ra($attributes, [
                    'parent_id' => 0,
                ]);

                $new = AssignmentModel::create($attributes);

                Cats::createItem($catId, $new);

                $local->source_id = $new->id;
                $local->save();

                self::saveAsGlobalRecursion($new, $local->nested()->orderBy('position')->get());
            });

            return $target;
        }
    }

    private static function saveAsGlobalRecursion($target, $nested)
    {
        foreach ($nested as $source) {
            $attributes = $source->used()->toArray();
            $new = $target->nested()->create($attributes);

            self::saveAsGlobalRecursion($new, $source->nested);
        }
    }

    public static function saveAsLocal($containerId, $returnerSourceId)
    {
        $container = Assignments::getContainer($containerId);
        $prototype = Assignments::getReturner($returnerSourceId);

        if ($container && $prototype) {
            $new = null;

            \DB::transaction(function () use ($prototype, $container, &$new) {
                $attributes = $prototype->toArray();

                ra($attributes, [
                    'parent_id' => $container->id,
                    'source_id' => $prototype->id
                ]);

                $new = AssignmentModel::create($attributes);

                self::saveAsLocalRecursion($new, $prototype->nested()->orderBy('position')->get());
            });

            return $new;
        }
    }

    private static function saveAsLocalRecursion($target, $nested)
    {
        foreach ($nested as $source) {
            $attributes = $source->used()->toArray();
            $new = $target->nested()->create($attributes);

            self::saveAsLocalRecursion($new, $source->nested);
        }
    }

    public static function useGlobal($assignmentId)
    {
        $assignment = AssignmentModel
            ::where('source_id', '>', 0)// только имеющий ссылку на глобальный может переключаться
            ->where('id', $assignmentId)
            ->first();

        if ($assignment) {
            $assignment->source_used = true;
            $assignment->save();

            return $assignment;
        }
    }

    public static function useLocal($assignmentId)
    {
        $assignment = AssignmentModel
            ::where('source_id', '>', 0)// только имеющий ссылку на глобальный может переключаться
            ->where('id', $assignmentId)
            ->first();

        if ($assignment) {
            $assignment->source_used = false;
            $assignment->save();

            return $assignment;
        }
    }

    public static function toggleEnabled($assignmentId)
    {
        if ($assignment = AssignmentModel::find($assignmentId)) {
            $assignment->enabled = !$assignment->enabled;
            $assignment->save();

            return $assignment;
        }
    }

    public static function toggleCacheEnabled($assignmentId)
    {
        if ($assignment = AssignmentModel::find($assignmentId)) {
            $assignment->cache_enabled = !$assignment->cache_enabled;
            $assignment->save();

            return $assignment;
        }
    }

    public static function toggleRequired($assignmentId)
    {
        if ($assignment = AssignmentModel::find($assignmentId)) {
            $assignment->required = !$assignment->required;
            $assignment->save();

            return $assignment;
        }
    }

    public static function updateValue($assignmentId, $value)
    {
        if ($assignment = AssignmentModel::find($assignmentId)) {
            $assignment->value = $value;
            $assignment->save();

            return $assignment;
        }
    }

    public static function getData($assignmentId, $path = false)
    {
        if ($assignment = AssignmentModel::find($assignmentId)) {
            $data = _j($assignment->data);

            return ap($data, $path);
        }
    }

    public static function updateData($assignmentId, $path, $value)
    {
        if ($assignment = AssignmentModel::find($assignmentId)) {
            $data = _j($assignment->data);

            ap($data, $path, $value);

            $assignment->data = j_($data);
            $assignment->save();

            return $assignment;
        }
    }
}
