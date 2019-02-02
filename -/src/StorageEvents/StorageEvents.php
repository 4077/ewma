<?php namespace ewma\StorageEvents;

use ewma\Controllers\Controller;
use ewma\Service\Service;

class StorageEvents extends Service
{
    public function getDispatcher($eventPath, $eventFilter, Controller $controller)
    {
        return new Dispatcher($eventPath, $eventFilter, $controller);
    }

    private $usedInstances = [];

    public function addInstance($instance)
    {
        merge($this->usedInstances, $instance);
    }

    public function getInstances()
    {
        return $this->usedInstances;
    }
}
