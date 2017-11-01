<?php namespace ewma\routers\ui\routerSettings\controllers;

use ewma\routers\models\Router as RouterModel;

class Main extends \Controller
{
    private $context;
    private $router;
    private $contextData;

    public function __create()
    {
        if ($this->data('context') && $router = RouterModel::find($this->data('router_id'))) {
            $this->context = $this->data['context'];
            $this->router = $router;
            $this->contextData = &$this->d('contexts:|' . $this->context);

            if ($callbacks = $this->data('callbacks')) {
                foreach ($callbacks as $event => $call) {
                    $this->contextData['callbacks'][$event] = $this->_caller()->_abs($call);
                }
            }
        }
    }

    public function view()
    {
        $v = $this->v();

        $v->assign([
                       'NAME_TXT' => $this->c('\std\ui txt:view', [
                           'path'              => 'input:nameUpdate',
                           'data'              => [
                               'context'   => $this->context,
                               'router_id' => $this->router->id
                           ],
                           'class'             => 'name_txt',
                           'fitInputToClosest' => '.value',
                           'content'           => $this->router->name
                       ]),
                   ]);

        $this->css();

        return $v;
    }
}
