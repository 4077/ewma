<?php namespace ewma\Route;

class RouteMatch
{
    private $data;

    private $route;

    public function __construct($data, $route)
    {
        $this->data = $data;
        $this->route = explode('/', trim_slashes($route));
    }

    public function data($varName = null)
    {
        if (null !== $varName) {
            if (isset($this->data[$varName])) {
                return $this->data[$varName];
            }
        } else {
            return $this->data;
        }
    }

    public function route($index = null)
    {
        if (null === $index) {
            if (isset($this->route[$index])) {
                return $this->route[$index];
            }
        } else {
            return $this->route;
        }
    }
}
