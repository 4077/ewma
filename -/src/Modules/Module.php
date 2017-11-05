<?php namespace ewma\Modules;

use ewma\Controllers\Controller;

class Module
{
    public $namespace;

    public $path;

    public $type;

    public $location;

    public $externalPath;

    public $masterModulePath = false;

    public $lessAutoImport;

    public $config = [];

    public $helpers = false;

    private $controller;

    public static function create($settings)
    {
        $module = new self;

        \ewma\Data\Data::extract($module, $settings, '
            namespace           namespace,
            path                path,
            type                type,
            location            location,
            externalPath        external_path,
            masterModulePath    master_module_path,
            lessAutoImport      less_auto_import,
            config              config,
            helpers             helpers
        ');

        return $module;
    }

    public function toCacheFormat()
    {
        return [
            'namespace'          => $this->namespace,
            'path'               => $this->path,
            'type'               => $this->type,
            'location'           => $this->location,
            'external_path'      => $this->externalPath,
            'master_module_path' => $this->masterModulePath,
            'less_auto_import'   => $this->lessAutoImport,
            'config'             => $this->config,
            'helpers'            => $this->helpers
        ];
    }

    public function getController()
    {
        if (!$this->controller) {
            $controller = new Controller;

            $controller->__meta__->moduleNamespace = $this->namespace;
            $controller->__meta__->modulePath = $this->path;
            $controller->__meta__->nodePath = '-';
            $controller->__meta__->absPath = '/' . $this->path . ' -';
            $controller->__meta__->nodeId = str_replace('\\', '_', $this->namespace) . '__-';

            $controller->__meta__->module = $this;

            $this->controller = $controller;
        }

        return $this->controller;
    }

    public function getDir()
    {
        if ($this->location == 'local') {
            return abs_path('modules', $this->path);
        }

        if ($this->location == 'vendor') {
            return abs_path('modules-vendor', $this->path);
        }

        if ($this->location == 'external') {
            return implode('/', [$this->externalPath, $this->path]);
        }
    }
}
