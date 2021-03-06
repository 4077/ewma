<?php namespace ewma\Modules;

use ewma\Controllers\Controller;

class Module
{
    public $id;

    public $parentId;

    public $namespace;

    public $path;

    public $dir;

    public $type;

    public $location;

    public $vendor;

    public $virtual;

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
            id                  id,
            parentId            parent_id,
            namespace           namespace,
            path                path,
            dir                 dir,
            type                type,
            location            location,
            vendor              vendor,
            virtual             virtual,
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
            'id'                 => $this->id,
            'parent_id'          => $this->parentId,
            'namespace'          => $this->namespace,
            'path'               => $this->path,
            'dir'                => $this->dir,
            'type'               => $this->type,
            'location'           => $this->location,
            'vendor'             => $this->vendor,
            'virtual'            => $this->virtual,
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
}
