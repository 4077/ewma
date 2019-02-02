<?php namespace ewma\models;

class Session extends \Model
{
    public $table = 'ewma_sessions';

    public function getDataAttribute($value)
    {
        return json_decode($value, true);
    }
}
