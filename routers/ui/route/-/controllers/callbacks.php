<?php namespace ewma\routers\ui\route\controllers;

class Callbacks extends \Controller
{
    public function setObsolete()
    {
        $this->console('obsolete ' . $this->data['route_id']);
    }
}
