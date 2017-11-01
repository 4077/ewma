<?php namespace ewma\routers\controllers\compiler;

class Router extends \Controller
{
    private $routes;

    public function compile($router)
    {
        if ($rootRoute = $router->routes()->where('parent_id', 0)->first()) {
            $this->compileRecursion($rootRoute);

            awrite(abs_path('cache/routers/' . $router->id . '.php'), $this->routes);
        }
    }

    private $pattern;

    private function compileRecursion($route)
    {
        $this->pattern[] = $route->pattern;

        $nested = $route->nested()->where('enabled', true)->orderBy('position')->get();
        foreach ($nested as $nestedRoute) {
            $this->compileRecursion($nestedRoute);
        }

        array_pop($this->pattern);

        $this->compileRoute($route);
    }

    public function compileRoute($route)
    {
        if ($this->pattern) {
            $pattern = trim_l_slash(implode('/', $this->pattern) . '/' . $route->pattern);
        } else {
            $pattern = '';
        }

        $this->routes[$route->id] = $this->c('@route')->compile($route, $pattern);
    }
}
