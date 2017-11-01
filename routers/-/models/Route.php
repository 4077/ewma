<?php namespace ewma\routers\models;

class Route extends \Model
{
    protected $table = 'ewma_routers_routes';

    public function router()
    {
        return $this->belongsTo(Router::class);
    }

    public function nested()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
}

class RouteObserver
{
    public function creating($model)
    {
        $position = Route::max('position') + 10;

        $model->position = $position;
    }
}

Route::observe(new RouteObserver);
