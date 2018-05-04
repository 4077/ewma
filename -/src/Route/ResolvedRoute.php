<?php namespace ewma\Route;

use ewma\Controllers\Controller;

class ResolvedRoute
{
    private $controller;

    private $data;

    public $routeTail;

    public $baseRoute;

    public function __construct(Controller $controller, $data, $routeTail, $baseRoute)
    {
        $this->controller = $controller;
        $this->data = $data;
        $this->routeTail = $routeTail;
        $this->baseRoute = $baseRoute;
    }

    public function data($path = false, $value = null)
    {
        if (null !== $value) {
            ap($this->data, $path, $value);

            return $this;
        } else {
            return ap($this->data, $path);
        }
    }

    public function to($callPath, $data = [], $responseCallback = null)
    {
        list($path, $method, $args) = array_pad(explode(':', $callPath), 3, null);

        $callData = $this->data;

        ra($callData, $data);

        $controller = $this->controller->c($path, $callData);

        if (null !== $args) {
            $controller->__meta__->setArgs($args);
        }

        $controller->__meta__->route = $this->routeTail;
        $controller->__meta__->baseRoute = $this->baseRoute;

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