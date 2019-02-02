<?php namespace ewma\Data;

class Cell
{
    public $model;

    public $field;

    public $pack;

    public function __construct($model, $field, $pack)
    {
        $this->model = $model;
        $this->field = $field;
        $this->pack = $pack;
    }

    public function value($set = null)
    {
        if (null === $set) {
            return $this->model->{$this->field};
        } else {
            $this->model->{$this->field} = $set;
            $this->model->save();
        }
    }

    public function setNull()
    {
        $this->model->{$this->field} = null;
        $this->model->save();
    }

    public function underscore()
    {
        return underscore_cell($this->model, $this->field);
    }

    public function underscoreField()
    {
        return underscore_field($this->model, $this->field);
    }
}
