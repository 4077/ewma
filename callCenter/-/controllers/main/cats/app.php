<?php namespace ewma\callCenter\controllers\main\cats;

class App extends \Controller
{
    public function getQueryBuilder()
    {
        return \ewma\callCenter\models\Cat::orderBy('position');
    }
}
