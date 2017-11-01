<?php namespace ewma\callCenter\models;

class Cat extends \Model
{
    protected $table = 'ewma_call_center_cats';

    public function nested()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function calls()
    {
        return $this->hasMany(Call::class);
    }
}

class CatObserver
{
    public function creating($model)
    {
        $position = Cat::max('position') + 10;

        $model->position = $position;
    }
}

Cat::observe(new CatObserver);
