<?php namespace ewma\access\ui\permissions\controllers\main;

class App extends \Controller
{
    public function selectModule()
    {
        if ($module = $this->app->modules->getByPath($this->data('module_path'))) {
            $this->s('~:selected_module_path|', $module->path, RA);

            $this->c('~:reload|');
        }
    }
}
