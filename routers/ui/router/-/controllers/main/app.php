<?php namespace ewma\routers\ui\router\controllers\main;

use ewma\routers\models\Route as RouteModel;

class App extends \Controller
{
    public function treeQueryBuilder()
    {
        return RouteModel::orderBy('position');
    }
}
