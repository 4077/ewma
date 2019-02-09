<?php namespace ewma\controllers\main;

class Modules extends \Controller
{
    public function create()
    {
        if ($path = $this->data('path')) {
            $type = false;

            if ($this->dataHas('master')) {
                $type = 'master';
            }

            if ($this->dataHas('slave')) {
                $type = 'slave';
            }

            $report = app()->modules->dev->create($path, $this->data('ns'), $type);

            if ($this->data('reset')) {
                $report[] = $this->c('\ewma~cache:reset');
            }

            return $report;
        }
    }

    public function install()
    {
        if ($namespace = $this->data('namespace') ?: $this->data('ns')) {
            $module = $this->app->modules->getByNamespace($namespace);

            include $module->dir . '/-/data/install/main.php';

            $installerClassName = '\\' . $namespace . '\installer\Main';

            $installer = new $installerClassName;

            $installer->install();
        }
    }
}
