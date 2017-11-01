<?php namespace ewma\dev\ui\modulesTree\controllers\main;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    public function toggleSubnodes()
    {
        $s = &$this->s('~|');

        toggle($s['expand_nodes'], $this->data['module_path']);

        $this->c('~:reload|');
    }

    public function selectModule()
    {
        $s = &$this->s('~|');

        $s['selected_module_path'] = $this->data('module_path');

        $this->c('<|')->performCallback('select', [
            'module_path' => $this->data('module_path')
        ]);
    }
}
