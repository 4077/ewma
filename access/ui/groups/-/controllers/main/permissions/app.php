<?php namespace ewma\access\ui\groups\controllers\main\permissions;

class App extends \Controller
{
    public function treeQueryBuilder()
    {
        return \ewma\models\access\Permission::orderBy('position');
    }
}
