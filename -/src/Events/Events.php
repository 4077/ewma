<?php namespace ewma\Events;

use ewma\App\App;
use ewma\Controllers\Controller;
use ewma\Service\Service;

class Events extends Service
{
    protected $services = ['app'];

    /**
     * @var App
     */
    public $app = App::class;

    public $bindings = [];

    public function bind($eventPath, \Closure $callback)
    {
        $bindings = &ap($this->bindings, $eventPath);
        $bindings[] = $callback;
    }

    public function unbind($eventPath)
    {
        ap($this->bindings, $eventPath, []);
    }

    public function rebind($eventPath, \Closure $callback)
    {
        $this->unbind($eventPath);
        $this->bind($eventPath, $callback);
    }

    public function trigger()
    {
        $args = func_get_args();

        if (isset($args[0])) {
            $eventPath = $args[0];

            $bindings = a2f(ap($this->bindings, $eventPath));
            foreach ($bindings as $callback) {
                call_user_func_array($callback, array_slice($args, 1));
            }
        }
    }

    public $callBindings = [];

    public $hasOnceCallBinding = false;

    public function bindCall(Controller $controller, $path, \Closure $callback)
    {
        $methodAbsPath = $controller->_p($path);

        $bindings = &ap($this->callBindings, $methodAbsPath);
        $bindings[] = $callback;

        $this->hasOnceCallBinding = true;
    }

    public function triggerCallBinding(Controller $controller, $method, $args)
    {
        $methodAbsPath = $controller->_methodAbsPath($method);

        $bindings = a2f(ap($this->callBindings, $methodAbsPath));
        foreach ($bindings as $callback) {
            call_user_func_array($callback, array_slice($args, 1));
        }
    }
}
