<?php namespace ewma\controllers\main;

class Configs extends \Controller
{
    public function addFromModules()
    {
        return $this->app->configs->addFromModules();
    }

    public function rewriteFromModules()
    {
        return $this->app->configs->rewriteFromModules();
    }

    public function updateFromModules()
    {
        return $this->app->configs->updateFromModules();
    }

    public function createAbsentProfiles()
    {
        return $this->app->configs->createAbsentProfiles();
    }

    public function addProfile()
    {

    }

    public function removeProfile()
    {

    }
}
