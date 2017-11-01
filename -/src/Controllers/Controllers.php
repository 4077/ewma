<?php namespace ewma\Controllers;

use ewma\App\App;
use ewma\Service\Service;

class Controllers extends Service
{
    protected $services = ['app'];

    /**
     * @var App
     */
    public $app = App::class;

    private $increment = 1;

    private $prototypes = [];

    private $controllersById = [];

    private $singletonesByPath = [];

    public function getById($controllerId)
    {
        if (isset($this->controllersById[$controllerId])) {
            return $this->controllersById[$controllerId];
        }
    }

    public function call($callPath, $data, Controller $caller)
    {
        list($callPath, $instance) = $this->explodeToPathAndInstance($callPath, $caller);
        list($path, $method, $args) = array_pad(explode(':', $callPath), 3, null);

        $absPath = $this->app->paths->resolve($path, $caller->__meta__->absPath);

        if (isset($this->singletonesByPath[$absPath])) {
            /**
             * @var $controller \ewma\Controllers\Controller
             */
            $controller = $this->singletonesByPath[$absPath];

            $controller->__meta__->callerId = $caller->__meta__->id;
            $controller->__meta__->calledMethod = $method;

            $controller->__meta__->setArgs($args);
            $controller->__meta__->setData($data);
            $controller->__meta__->instance = $instance;

            $controller->__meta__->route = $caller->__meta__->route;
            $controller->__meta__->baseRoute = $caller->__meta__->baseRoute;

            $controller->__recreate();

            if ($method) {
                return $controller->__run__($method);
            } else {
                return $controller;
            }
        } elseif ($prototype = $this->getPrototype($absPath)) {
            /**
             * @var $controller \ewma\Controllers\Controller
             */
            $controller = clone $prototype;

            $controllerId = ++$this->increment;
            $this->controllersById[$controllerId] = $controller;

            $controller->__meta__->id = $controllerId;

            $controller->__meta__->callerId = $caller->__meta__->id;
            $controller->__meta__->calledMethod = $method;

            $controller->__meta__->setArgs($args);
            $controller->__meta__->setData($data);
            $controller->__meta__->instance = $instance;

            $controller->__meta__->route = $caller->__meta__->route;
            $controller->__meta__->baseRoute = $caller->__meta__->baseRoute;

            $controller->__create();

            if ($prototype->singleton) {
                $this->singletonesByPath[$absPath] = $controller;
            }

            if ($method) {
                return $controller->__run__($method);
            } else {
                return $controller;
            }
        } else {
            throw new \Exception('Controller ' . $absPath . ' not exists');
        }
    }

    private function getPrototype($absPath)
    {
        if (!isset($this->prototypes[$absPath])) {
            list($modulePath, $nodePath) = $this->app->paths->separateAbsPath($absPath);

            $module = $this->app->modules->getByPath($modulePath);

            if (null !== $module) {
                $controllerClassName = $module->namespace . '\\controllers\\' . implode('\\', explode('/', $nodePath));

                /**
                 * @var $controller \ewma\Controllers\Controller
                 */
                if (class_exists($controllerClassName)) {
                    $controller = new $controllerClassName;
                } else {
                    $controller = new Controller;

                    $controller->__meta__->virtual = true;
                }

                $nodeNs = str_replace('\\', '_', $module->namespace);

                $controller->__meta__->moduleNamespace = $module->namespace;
                $controller->__meta__->modulePath = $modulePath;
                $controller->__meta__->nodePath = $nodePath;
                $controller->__meta__->absPath = $absPath;
                $controller->__meta__->nodeNs = $nodeNs;
                $controller->__meta__->nodeId = $nodeNs . '__' . str_replace('/', '_', $nodePath);
                $controller->__meta__->module = $module;

                $this->prototypes[$absPath] = $controller;
            } else {
                throw new \Exception('Module ' . $modulePath . ' not found');
            }
        }

        return $this->prototypes[$absPath];
    }

    public function explodeToPathAndInstance($path, Controller $caller)
    {
        if (false !== strpos($path, '|')) {
            list($path, $instance) = explode('|', $path);

            if (!$instance) {
                $instance = $caller->__meta__->instance;
            }
        }

        if (!isset($instance)) {
            $instance = '';
        }

        return [$path, $instance];
    }

    public function renderAbsPath($callPath, Controller $caller)
    {
        if (false !== strpos($callPath, '|')) {
            list($callPath, $instance) = explode('|', $callPath);

            if (!$instance) {
                $instance = $caller->__meta__->instance;
            }
        }

        list($path, $method, $args) = array_pad(explode(':', $callPath), 3, null);

        $absPath = $this->app->paths->resolve($path, $caller->__meta__->absPath);

        $output = $absPath . ($method ? ':' . $method : '') . ($args !== null ? ':' . $args : '') . (!empty($instance) ? '|' . $instance : '');

        return $output;
    }
}
