<?php namespace ewma\routers\controllers;

use ewma\routers\models\Router as RouterModel;

class Compiler extends \Controller
{
    public function compile()
    {
        $enabledRouters = [];

        if ($routers = RouterModel::orderBy('position')->get()) {
            foreach ($routers as $router) {
                if ($router->enabled) {
                    $enabledRouters[] = $router->id;
                }

                $this->c('>router')->compile($router);
            }

            awrite(abs_path('cache/routers/routers.php'), $enabledRouters);
        }
    }
}
