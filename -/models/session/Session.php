<?php namespace ewma\models\session;

class Session extends \Model
{
    public $table = 'ewma_sessions';

    public function getDataAttribute($value)
    {
        return json_decode($value, true);
    }
}
