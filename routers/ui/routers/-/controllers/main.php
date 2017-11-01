<?php namespace ewma\routers\ui\routers\controllers;

use ewma\routers\models\Router as RouterModel;

class Main extends \Controller
{
    private $context;
    private $contextData;

    public function __create()
    {
        if ($this->data('context')) {
            $this->context = $this->data['context'];
            $this->contextData = &$this->d('contexts:|' . $this->context);

            if ($callbacks = $this->data('callbacks')) {
                foreach ($callbacks as $event => $call) {
                    $this->contextData['callbacks'][$event] = $this->_caller()->_abs($call);
                }
            }
        } else {
            $this->lock();
        }
    }

    public function reload()
    {
        $this->jquery()->replace($this->view());
    }

    public function view()
    {
        $v = $this->v();

        $routers = RouterModel::orderBy('position')->get();

        foreach ($routers as $router) {
            $v->assign('router', [
                'ID'      => $router->id,
                'CONTENT' => $this->c('>router:view', [
                    'context'  => $this->context,
                    'router'   => $router,
                    'selected' => $router->id == $this->data['selected_router_id']
                ])
            ]);

            $this->c('\std\ui button:bind', [
                'selector' => $this->_selector(". .router[router_id='" . $router->id . "']"),
                'path'     => 'input:select',
                'data'     => [
                    'context'   => $this->context,
                    'router_id' => $router->id
                ]
            ]);
        }

        $this->c('\std\ui sortable:bind', [
            'selector'       => $this->_selector(". .routers"),
            'path'           => 'input:reorder',
            'items_id_attr'  => 'router_id',
            'plugin_options' => [
                'axis' => 'y'
            ]
        ]);

        $v->assign([
                       'CREATE_BUTTON'  => $this->c('\std\ui button:view', [
                           'path'    => 'input:create',
                           'data'    => ['context' => $this->context],
                           'class'   => 'button create',
                           'content' => 'Создать'
                       ]),
                       'COMPILE_BUTTON' => $this->c('\std\ui button:view', [
                           'path'    => 'input:compile',
                           'class'   => 'compile_button',
                           'content' => 'Скомпилировать'
                       ]),
                   ]);

        $this->css(':\css\std~');

        return $v;
    }
}
