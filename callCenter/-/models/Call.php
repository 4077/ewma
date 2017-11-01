<?php namespace ewma\callCenter\models;

class Call extends \Model
{
    protected $table = 'ewma_call_center_calls';

    public function cat()
    {
        return $this->belongsTo(Cat::class);
    }
}

class CallObserver
{
    public function creating($model)
    {
        $position = Call::max('position') + 10;

        $model->position = $position;
    }
}

Call::observe(new CallObserver);
