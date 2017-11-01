<?php namespace ewma\controllers;

class Configs extends \Controller
{
    public function addFromModules()
    {
        return $this->app->configs->updateFromModules(AA);
    }

    public function updateFromModules()
    {
        return $this->app->configs->updateFromModules(RA);
    }

    public function rewriteFromModules()
    {
        return $this->app->configs->updateFromModules(RR);
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
