<?php namespace ewma\routers\models;

class Router extends \Model
{
    protected $table = 'ewma_routers';

    public function routes()
    {
        return $this->hasMany(Route::class);
    }
}

class RouterObserver
{
    public function creating($model)
    {
        $position = Router::max('position') + 10;

        $model->position = $position;
    }
}

Router::observe(new RouterObserver);
