<?php namespace ewma\routers\controllers;

class Main extends \Controller
{
    public function compile()
    {
        $this->c('compiler:compile');
    }

    public function compileRouter()
    {
        if ($router = \ewma\routers\models\Router::find($this->data('router_id'))) {
            $this->c('compiler/router')->compile($router);
        }
    }

    public function compileRoute()
    {
        if ($route = \ewma\routers\models\Route::find($this->data('route_id'))) {
            while (!$router = $route->router and $route->parent) {
                $route = $route->parent;
            }

            if (isset($router)) {
                $this->c('compiler/router')->compile($route->router);
            }
        }
    }
}
