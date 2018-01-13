<?php namespace ewma\dev\ui\node\controllers\main\content\data;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    public function selectInstance()
    {
        $this->smap('~|', 'module_path, node_path, type');

        $instance = $this->data['module_path'] . '/_/' . $this->data['node_path'];

        $s = &$this->s('<|' . $instance);

        $s['node_instance'] = $this->data['node_instance'];

        $this->c('~:reload|');
    }
}
