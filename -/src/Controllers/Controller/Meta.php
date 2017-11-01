<?php namespace ewma\Controllers\Controller;

class Meta
{
    private $controller;

    public function __construct(\ewma\Controllers\Controller $controller)
    {
        $this->controller = $controller;
    }

    public function setController(\ewma\Controllers\Controller $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Номер контроллера
     */
    public $id;

    /**
     * Номер контроллера, создавшего этот контроллер
     */
    public $callerId;

    /**
     * Экземпляр
     *
     * @var string
     */
    public $instance = '';

    public $moduleNamespace;

    public $modulePath;

    public $nodePath;

    public $absPath;

    public $nodeId;

    public $nodeNs;

    public $virtual = false;

    /**
     * @var \ewma\Modules\Module
     */
    public $module;

    /**
     * @var \ewma\Modules\Module
     */
    public $masterModule;

    /**
     * Метод, который был вызван при создании или пересоздании
     *
     * @var string
     */
    public $calledMethod;

    /**
     * Допустимые типы источников вызова метода call на этом контроллере
     *
     * @var string
     */
    public $allowForCallPerform = \ewma\Controllers\Controller::APP;

    /**
     * Блокировка запуска вызванного метода
     */
    public $locked;

    public $route = null;

    public $baseRoute = null;

    public $routeResponse = null;

    /**
     * Аргументы, с которыми был вызван метод
     *
     * @var array
     */
    public $args = [];

    /**
     * @param array $input
     */
    public function setArgs($input = [])
    {
        if (null !== $input) {
            $this->args = l2a($input);
        }
    }

    /**
     * @param array $input
     */
    public function setData($input = [])
    {
        if (is_array($input)) {
            $this->controller->data = $input;
        }
    }
}
