<?php namespace ewma\models\storage;

class Storage extends \Model
{
    public $table = 'ewma_storage';

    public function getDataAttribute($value)
    {
        return json_decode($value, true);
    }
}
