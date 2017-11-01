<?php namespace ewma\routers;

use ewma\routers\models\Router as RouterModel;

class Routers
{
    public static function create()
    {
        $router = RouterModel::create([]);

        $router->routes()->create([
                                      'parent_id' => 0,
                                      'enabled'   => true
                                  ]);

        return $router;
    }

    public static function getRootRoute(RouterModel $router)
    {
        $route = $router->routes()->where('parent_id', 0)->first();

        if (!$route) {
            $route = $router->routes()->create(['parent_id' => 0]);
        }

        return $route;
    }

    public static function delete($routerId)
    {
        if ($router = RouterModel::find($routerId)) {
            $rootRoute = $router->routes()->where('parent_id', 0)->first();

            Routes::delete($rootRoute->id);

            $router->delete();
        }
    }

    public static function toggleEnabled($routerId)
    {
        if ($router = RouterModel::find($routerId)) {
            $router->enabled = !$router->enabled;
            $router->save();

            return $router->enabled;
        }
    }

    public static function updateName($routerId, $name)
    {
        if ($router = RouterModel::find($routerId)) {
            $router->name = $name;
            $router->save();
        }
    }

    public static function reorder($sequence)
    {
        foreach ((array)$sequence as $n => $routerId) {
            if (is_numeric($n)) {
                if ($router = RouterModel::find($routerId)) {
                    $router->update(['position' => $n * 10]);
                }
            }
        }
    }
}
