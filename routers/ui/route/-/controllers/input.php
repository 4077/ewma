<?php namespace ewma\routers\ui\route\controllers;

use ewma\routers\Routes;
use ewma\routers\models\Route as RouteModel;

class Input extends \Controller
{
    public $allow = self::XHR;

    private function performCallback($event, $routeId)
    {
        if ($context = $this->data('context')) {
            if ($callback = $this->d('contexts:callbacks/' . $event . '|' . $context)) {
                $this->_call($callback)
                    ->data('context', $context)
                    ->data('route_id', $routeId)
                    ->perform();

                return true;
            }
        }
    }

    public function compile()
    {
        $this->c('^:compileRoute', false, 'route_id');
    }

    public function nameUpdate()
    {
        if ($this->dataHas('value')) {
            $txt = \std\ui\Txt::value($this);

            Routes::updateName($this->data('route_id'), $txt->value);

            $this->performCallback('update_name', $this->data['route_id']);
            $txt->response();
        }
    }

    public function patternUpdate()
    {
        if ($this->dataHas('value')) {
            $txt = \std\ui\Txt::value($this);

            Routes::updatePattern($this->data('route_id'), $txt->value);

            $txt->response();
        }
    }

    public function setTargetType($type = false)
    {
        Routes::updateTargetType($this->data('route_id'), $type);

        $this->performCallback('set_target_type', $this->data['route_id']);
    }

    public function targetMethodPathUpdate()
    {
        if ($this->dataHas('value')) {
            $txt = \std\ui\Txt::value($this);

            Routes::updateTargetMethodPath($this->data('route_id'), $txt->value);

            $txt->response();
        }
    }

    public function setResponseWrapper()
    {
        if (in($this->data('wrapper'), 'none, ewma_html')) {
            Routes::updateResponseWrapper($this->data('route_id'), $this->data['wrapper']);

            $this->c('~:reload', false, 'context, route_id');
        }
    }
}
