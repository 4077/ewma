<?php namespace ewma\Data;

class Cell
{
    public $model;

    public $field;

    public function __construct($model, $field)
    {
        $this->model = $model;
        $this->field = $field;
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

    private $underscore;

    public function underscore()
    {
        if (null === $this->underscore) {
            $this->underscore = underscore_cell($this->model, $this->field);
        }

        return $this->underscore;
    }

    private $underscoreField;

    public function underscoreField()
    {
        if (null === $this->underscoreField) {
            $this->underscoreField = underscore_field($this->model, $this->field);
        }

        return $this->underscoreField;
    }

    private $pack;

    public function pack()
    {
        if (null == $this->pack) {
            $this->pack = pack_cell($this->model, $this->field);
        }

        return $this->pack;
    }

    private $xpack;

    public function xpack()
    {
        if (null == $this->xpack) {
            $this->xpack = pack_cell($this->model, $this->field, true);
        }

        return $this->xpack;
    }
}
