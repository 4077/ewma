<?php namespace ewma\controllers\main;

class StorageEvents extends \Controller
{
//    private $eventPath;
//
//    private $eventName;
//
//    private $eventInstance;
//
//    private $eventFilter;

//    public $singleton = true;

    public function __create()
    {
        $this->eventPath = $this->data('event_path');
        $this->eventName = $this->data('event_name');
        $this->eventInstance = $this->data('event_instance');
        $this->eventFilter = $this->data('event_filter');
    }

    public function bind()
    {
        $bindings = &$this->d(':' . $this->data('event_instance') . '|' . $this->data('event_path'));

        $eventData = [
            'call' => [$this->data('path'), $this->data('data')]
        ];

        if ($this->data('event_filter')) {
            aa($eventData, [
                'filter' => $this->data('event_filter')
            ]);
        }

        if ($this->data('event_name')) {
            $bindings['.'][$this->data('event_name')] = $eventData;
        } else {
            $bindings['.'][] = $eventData;
        }

//        $this->app->storageEvents->addInstance($this->data('event_instance'));
    }

    public function unbind()
    {
        $s = &$this->d(':|' . $this->data('event_path'));

        if ($this->data('event_name')) {
            $binding = &ap($s, $this->data('event_instance') . '/./' . $this->data('event_name'));

            unset($binding);
        } else {
            ap($s, $this->data('event_instance') . '/.', []);
        }
    }

    public function rebind()
    {
        $this->unbind();
        $this->bind();
    }

    public function unbindNested() // потестить, и нужна ли она вообще
    {
        $bindings = &$this->d(':' . $this->data('event_instance') . '|' . $this->data('event_path'));

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
        $nodes = $this->d(':|' . $this->data('event_path'));

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
        if ($this->data('event_name')) {
            if ($binding = ap($node, $this->data('event_name'))) {
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

//            $this->log('');
//            $this->log('TRIGGER BINDING');
//            $this->log('        event path: ' . $this->data('event_path'));
//            $this->log('        event name: ' . $this->data('event_name'));
//            $this->log('    event instance: ' . $this->data('event_instance'));
//            $this->log('              call: ' . j_($binding['call']));
//            $this->log('      trigger data: ' . j_(pack_models($this->data('trigger_data'))));

            $call->perform();
        }
    }

    private function triggerFilter($bindingsFilter)
    {
        $bindingsFilterFlat = a2f($bindingsFilter);
        $eventFilterFlat = a2f($this->data('event_filter'));

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
