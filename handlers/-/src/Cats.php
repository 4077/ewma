<?php namespace ewma\handlers;

use ewma\handlers\models\Cat as CatModel;
use ewma\handlers\models\CatItem as CatItemModel;
use ewma\handlers\models\Assignment as AssignmentModel;

class Cats
{
    public static function create($catId)
    {
        if ($cat = CatModel::find($catId)) {
            return $cat->nested()->create([]);
        }
    }

    public static function createItem($catId, $assignment)
    {
        return CatItemModel::create([
                                        'cat_id'        => $catId,
                                        'assignment_id' => $assignment->id
                                    ]);
    }

    public static function updateName($catId, $name)
    {
        if ($cat = CatModel::find($catId)) {
            $cat->name = $name;
            $cat->save();
        }
    }
}
