<?php namespace ewma\dev\ui\node\controllers\main\content\generateControl;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    public function generate()
    {
        $this->c('\dev\project\module\generators~:generate', [
            'type'        => $this->data['type'],
            'template'    => $this->data['template'],
            'module_path' => $this->data['module_path'],
            'node_path'   => $this->data['node_path']
        ]);

        $this->c('~:reload|');
    }
}
