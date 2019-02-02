<?php namespace ewma\controllers\main;

class StorageEvents extends \Controller
{
    private $eventPath;

    private $eventName;

    private $eventInstance;

    private $eventFilter;

    public function __create()
    {
        $this->eventPath = $this->data('event_path');
        $this->eventName = $this->data('event_name');
        $this->eventInstance = $this->data('event_instance');
        $this->eventFilter = $this->data('event_filter');
    }

    public function bind()
    {
        $bindings = &$this->d(':' . $this->eventInstance . '|' . $this->eventPath);

        $eventData = [
            'call' => [$this->data('path'), $this->data('data')]
        ];

        if ($this->eventFilter) {
            aa($eventData, [
                'filter' => $this->eventFilter
            ]);
        }

        if ($this->eventName) {
            $bindings['.'][$this->eventName] = $eventData;
        } else {
            $bindings['.'][] = $eventData;
        }

//        $this->app->storageEvents->addInstance($this->eventInstance);
    }

    public function unbind()
    {
        $s = &$this->d(':|' . $this->eventPath);

        if ($this->eventName) {
            $binding = &ap($s, $this->eventInstance . '/./' . $this->eventName);

            unset($binding);
        } else {
            ap($s, $this->eventInstance . '/.', []);
        }
    }

    public function rebind()
    {
        $this->unbind();
        $this->bind();
    }

    public function unbindNested() // потестить, и нужна ли она вообще
    {
        $bindings = &$this->d(':' . $this->eventInstance . '|' . $this->eventPath);

        if (isset($bindings['.'])) {
            $bindings = [
                '.' => $bindings['.']
            ];
        } else {
            $bindings['.'] = [];
        }
    }

    public function trigger()
    {
        $nodes = $this->d(':|' . $this->eventPath);

        if ($nodes) {
            $this->triggerInstancesRecursion($nodes);
        }
    }

    private function triggerInstancesRecursion($nodes)
    {
        foreach ($nodes as $key => $node) {
            if ($key == '.') {
                $this->triggerBindings($node);
            } else {
                $this->triggerInstancesRecursion($node);
            }
        }
    }

    private function triggerBindings($node)
    {
        if ($this->eventName) {
            if ($binding = ap($node, $this->eventName)) {
                $this->triggerBinding($binding);
            }
        } else {
            foreach ((array)$node as $binding) {
                $this->triggerBinding($binding);
            }
        }
    }

    private function triggerBinding($binding)
    {
        if (!empty($binding['filter'])) {
            $trigger = $this->triggerFilter($binding['filter']);
        } else {
            $trigger = true;
        }

        if ($trigger) {
            $call = $this->_call($binding['call'])->aa($this->data('trigger_data'));

            $call->perform();
        }
    }

    private function triggerFilter($bindingsFilter)
    {
        $bindingsFilterFlat = a2f($bindingsFilter);
        $eventFilterFlat = a2f($this->eventFilter);

        $pass = true;
        foreach ($bindingsFilterFlat as $path => $value) {
            if (isset($eventFilterFlat[$path])) {
                $pass = $eventFilterFlat[$path] == $value;
            } else {
                $pass = false;
            }

            if (!$pass) {
                break;
            }
        }

        return $pass;
    }

    public function reset()
    {
        \ewma\models\Storage::where('module_namespace', 'ewma')->where('node_path', 'main/storageEvents')->delete();

        return 'reset storageEvents';
    }
}
