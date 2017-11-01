<?php namespace ewma\access\ui\userPermissions\controllers\main;

class App extends \Controller
{
    public function selectModule()
    {
        $s = &$this->s('~');

        $s['selected_module_path'] = $this->data('module_path');

        $this->c('~:reload', [], 'user');
    }
}
