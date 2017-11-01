<?php namespace ewma\routers\ui\controllers\main;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    public function updateRoutersWidth()
    {
        $s = &$this->s('~');

        $s['routers_width'] = $this->data('width');
    }

    public function updateRouterWidth()
    {
        $s = &$this->s('~');

        $s['router_width'] = $this->data('width');
    }
}
