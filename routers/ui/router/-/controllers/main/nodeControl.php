<?php namespace ewma\routers\ui\router\controllers\main;

use ewma\routers\models\Route as RouteModel;

class NodeControl extends \Controller
{
    private $route;

    public function __create()
    {
        if ($this->data('route') instanceof RouteModel) {
            $this->route = $this->data['route'];
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
        $v = $this->v('|' . $this->route->id);

        $isRootNode = $this->data['root_node_id'] == $this->route->id;

        $v->assign([
                       'ROOT_CLASS'            => $isRootNode ? 'root' : '',
                       'LISTEN_CLASS'          => $this->route->listen ? '' : 'listen_disabled',
                       'ENABLED_CLASS'         => in($this->route->id, $this->data['enabled_ids']) ? '' : 'disabled',
                       'NAME'                  => $this->route->name,
                       'DUPLICATE_BUTTON'      => $this->c('\std\ui button:view', [
                           'visible' => !$isRootNode,
                           'path'    => 'input:duplicate',
                           'data'    => [
                               'context'   => $this->data('context'),
                               'instance'  => $this->data('instance'),
                               'route_id'  => $this->route->id,
                               'router_id' => $this->data('router_id')
                           ],
                           'class'   => 'button duplicate',
                           'title'   => 'Создать копию',
                           'content' => '<div class="icon"></div>'
                       ]),
                       'TOGGLE_LISTEN_BUTTON'  => $this->c('\std\ui button:view', [
                           'visible' => !$isRootNode,
                           'path'    => 'input:toggleListen',
                           'data'    => [
                               'context'   => $this->data('context'),
                               'instance'  => $this->data('instance'),
                               'route_id'  => $this->route->id,
                               'router_id' => $this->data('router_id')
                           ],
                           'class'   => 'button toggle_listen ' . ($this->route->listen ? 'enabled' : ''),
                           'title'   => $this->route->listen ? 'Выключить прослушивание' : 'Включить прослушивание',
                           'content' => '<div class="icon"></div>'
                       ]),
                       'TOGGLE_ENABLED_BUTTON' => $this->c('\std\ui button:view', [
                           'visible' => !$isRootNode,
                           'path'    => 'input:toggleEnabled',
                           'data'    => [
                               'context'   => $this->data('context'),
                               'instance'  => $this->data('instance'),
                               'route_id'  => $this->route->id,
                               'router_id' => $this->data('router_id')
                           ],
                           'class'   => 'button toggle_enabled ' . ($this->route->enabled ? 'enabled' : ''),
                           'title'   => $this->route->listen ? 'Выключить' : 'Включить',
                           'content' => '<div class="icon"></div>'
                       ]),
                       'CREATE_BUTTON'         => $this->c('\std\ui button:view', [
                           'path'    => 'input:create',
                           'data'    => [
                               'context'   => $this->data('context'),
                               'instance'  => $this->data('instance'),
                               'route_id'  => $this->route->id,
                               'router_id' => $this->data('router_id')
                           ],
                           'class'   => 'button create',
                           'title'   => 'Создать',
                           'content' => '<div class="icon"></div>'
                       ]),
                       'EXCHANGE_BUTTON'       => $this->c('\std\ui button:view', [
                           'path'    => 'input:exchange',
                           'data'    => [
                               'context'   => $this->data('context'),
                               'instance'  => $this->data('instance'),
                               'route_id'  => $this->route->id,
                               'router_id' => $this->data('router_id')
                           ],
                           'class'   => 'button exchange',
                           'title'   => 'Импорт/экспорт',
                           'content' => '<div class="icon"></div>'
                       ]),
                       'DELETE_BUTTON'         => $this->c('\std\ui button:view', [
                           'visible' => !$isRootNode,
                           'path'    => 'input:delete',
                           'data'    => [
                               'context'   => $this->data('context'),
                               'instance'  => $this->data('instance'),
                               'route_id'  => $this->route->id,
                               'router_id' => $this->data('router_id')
                           ],
                           'class'   => 'button delete',
                           'title'   => 'Удалить',
                           'content' => '<div class="icon"></div>'
                       ])
                   ]);

        $this->css(':\jquery\ui icons');

        if (!$isRootNode) {
            $this->c('\std\ui button:bind', [
                'selector' => $this->_selector('|' . $this->route->id),
                'path'     => 'input:select',
                'data'     => [
                    'context'   => $this->data('context'),
                    'instance'  => $this->data('instance'),
                    'route_id'  => $this->route->id,
                    'router_id' => $this->data('router_id')
                ]
            ]);
        }

        return $v;
    }
}
