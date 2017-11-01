<?php namespace ewma\routers\ui\routerSettings\controllers;

use ewma\routers\Routers;

class Input extends \Controller
{
    public $allow = self::XHR;

    private function performCallback($event, $routerId)
    {
        if ($context = $this->data('context')) {
            $callback = $this->d('contexts:callbacks/' . $event . '|' . $context);

            if ($callback) {
                $this->_call($callback)->data('router_id', $routerId)->perform();
            }
        }
    }

    public function nameUpdate()
    {
        if ($this->dataHas('value')) {
            $txt = \std\ui\Txt::value($this);

            Routers::updateName($this->data('router_id'), $txt->value);

            $this->performCallback('update_name', $this->data('router_id'));
        }
    }
}
