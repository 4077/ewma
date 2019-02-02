<?php namespace ewma\Data;

class Field
{
    public $model;

    public $name;

    public $pack;

    public function __construct($model, $field, $pack)
    {
        $this->model = $model;
        $this->name = $field;
        $this->pack = $pack;
    }

    public function underscore()
    {
        return underscore_field($this->model, $this->name);
    }
}
