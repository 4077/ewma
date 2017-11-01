<?php namespace ewma\handlers\models;

class CatItem extends \Model
{
    protected $table = 'ewma_handlers_cats_items';

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function cat()
    {
        return $this->belongsTo(Assignment::class);
    }
}

class CatItemObserver
{
    public function creating($model)
    {
        $position = CatItem::max('position') + 10;

        $model->position = $position;
    }
}

CatItem::observe(new CatItemObserver);
