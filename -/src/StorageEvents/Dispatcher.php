<?php namespace ewma\StorageEvents;

use ewma\Controllers\Controller;

class Dispatcher
{
    private $eventPath;

    private $eventName;

    private $eventInstance;

    private $eventFilter;

    private $controller;

    public function __construct($eventPath, $eventFilter, Controller $controller)
    {
        list($path, $name, $instance) = $this->explodeToPathAndInstance($eventPath, $controller);

        $this->eventPath = $path;
        $this->eventName = $name;
        $this->eventInstance = $instance;
        $this->eventFilter = $eventFilter;

        $this->controller = $controller;
    }

    private function explodeToPathAndInstance($path, Controller $caller)
    {
        if (false !== strpos($path, '|')) {
            list($path, $eventInstance) = explode('|', $path);
        }

        list($eventPath, $eventName) = array_pad(explode(':', $path), 2, null);

        if (empty($eventInstance)) {
            $eventInstance = $caller->_nodeId();
        }

        return [$eventPath, $eventName, $eventInstance];
    }

    public function name($name)
    {
        $this->eventName = $name;

        return $this;
    }

    public function bind()
    {
        list($path, $data) = array_pad(func_get_args(), 2, null);

        if (null !== $path) {
            $this->controller->c('\ewma~storageEvents:bind', [
                'event_path'     => $this->eventPath,
                'event_name'     => $this->eventName,
                'event_filter'   => $this->eventFilter,
                'event_instance' => $this->eventInstance,
                'path'           => $this->controller->_p($path),
                'data'           => $data,
            ]);
        }

        return $this;
    }

    public function unbind()
    {
        $this->controller->c('\ewma~storageEvents~:unbind', [
            'event_path'     => $this->eventPath,
            'event_name'     => $this->eventName,
            'event_instance' => $this->eventInstance
        ]);

        return $this;
    }

    public function rebind()
    {
        list($path, $data) = array_pad(func_get_args(), 2, null);

        if (null !== $path) {
            $this->controller->c('\ewma~storageEvents:rebind', [
                'event_path'     => $this->eventPath,
                'event_name'     => $this->eventName,
                'event_instance' => $this->eventInstance,
                'event_filter'   => $this->eventFilter,
                'path'           => $this->controller->_p($path),
                'data'           => $data
            ]);
        }

        return $this;
    }

    public function unbindNested()
    {
        $this->controller->c('\ewma~storageEvents:unbindNested', [
            'event_path'     => $this->eventPath,
            'event_instance' => $this->eventInstance
        ]);

        return $this;
    }

    public function trigger($data = [])
    {
        $this->controller->c('\ewma~storageEvents:trigger', [
            'event_path'   => $this->eventPath,
            'event_name'   => $this->eventName,
            'event_filter' => $this->eventFilter,
            'trigger_data' => $data
        ]);

        return $this;
    }
}
