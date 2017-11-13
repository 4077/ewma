<?php namespace ewma\Route;

use ewma\App\App;
use ewma\Controllers\Controller;

class ResolvedRoute
{
    private $app;

    private $controller;

    private $setData;

    private $setRoute;

    private $setBaseRoute;

    public function __construct(Controller $controller, $setData, $setRoute, $setBaseRoute)
    {
        $this->app = App::getInstance();

        $this->controller = $controller;
        $this->setData = $setData;
        $this->setRoute = $setRoute;
        $this->setBaseRoute = $setBaseRoute;
    }

    public function to($callPath, $data = [], $responseCallback = null)
    {
        list($path, $method, $args) = array_pad(explode(':', $callPath), 3, null);

        $setData = $this->setData;
        ra($setData, $data);

        $controller = $this->controller->c($path, $setData);

        if (null !== $args) {
            $controller->__meta__->setArgs($args);
        }

        $controller->__meta__->route = $this->setRoute;
        $controller->__meta__->baseRoute = $this->setBaseRoute;

        if ($method) {
            $response = $controller->__run__($method);
        } else {
            $response = $controller;
        }

        if (null !== $responseCallback) {
            $response = call_user_func($responseCallback, $response);
        }

        $this->controller->__meta__->routeResponse = $response;
    }

    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->controller, $method], $parameters);
    }
}