<?php namespace ewma\routers\ui\routers\controllers\main;

use ewma\routers\models\Router as RouterModel;

class Router extends \Controller
{
    private $router;

    public function __create()
    {
        if ($this->data('router') instanceof RouterModel) {
            $this->router = $this->data['router'];
        } else {
            $this->lock();
        }
    }

    public function view()
    {
        $v = $this->v();

        $v->assign([
                       'ID'                    => $this->router->id,
                       'NAME'                  => $this->router->name ? $this->router->name : '...',
                       'SELECTED_CLASS'        => $this->data['selected'] ? 'selected' : '',
                       'DISABLED_CLASS'        => $this->router->enabled ? '' : 'disabled',
                       'TOGGLE_ENABLED_BUTTON' => $this->c('\std\ui button:view', [
                           'path'    => 'input:toggleEnabled',
                           'data'    => [
                               'context'   => $this->data['context'],
                               'router_id' => $this->router->id
                           ],
                           'title'   => $this->router->enabled ? 'Выключить' : 'Включить',
                           'class'   => 'button toggle_enabled ' . ($this->router->enabled ? 'enabled' : ''),
                           'content' => '<div class="icon"></div>'
                       ]),
                       'DELETE_BUTTON'         => $this->c('\std\ui button:view', [
                           'path'    => 'input:delete',
                           'data'    => [
                               'context'   => $this->data['context'],
                               'router_id' => $this->router->id
                           ],
                           'title'   => 'Удалить',
                           'class'   => 'button delete',
                           'content' => '<div class="icon"></div>'
                       ]),
                   ]);

        $this->css(':\jquery\ui icons');

        return $v;
    }
}
