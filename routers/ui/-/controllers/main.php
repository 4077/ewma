<?php namespace ewma\routers\ui\controllers;

class Main extends \Controller
{
    private $s;

    public function __create()
    {
        $this->a('ewma\routers:') or $this->lock();

        $this->s = &$this->s(false, [
            'selected_router_id'             => false,
            'selected_route_id_by_router_id' => false,
            'routers_width'                  => 250,
            'router_width'                   => 450,
        ]);
    }

    private function getSelectedRouterId()
    {
        return $this->s['selected_router_id'];
    }

    private function getSelectedRouteId()
    {
        $selectedRouterId = $this->getSelectedRouterId();

        if (!isset($this->s['selected_route_id_by_router_id'][$selectedRouterId])) {
            $this->s['selected_route_id_by_router_id'][$selectedRouterId] = false;
        }

        return $this->s['selected_route_id_by_router_id'][$selectedRouterId];
    }

    public function reload()
    {
        $this->jquery()->replace($this->view());
    }

    public function view()
    {
        $v = $this->v();

        $context = $this->_nodeId();

        $selectedRouterId = $this->getSelectedRouterId();
        $selectedRouteId = $this->getSelectedRouteId();

        $v->assign([
                       'ROUTERS_WIDTH' => $this->s['routers_width'],
                       'ROUTERS'       => $this->c('routers~:view', [
                           'context'            => $context,
                           'selected_router_id' => $selectedRouterId,
                           'callbacks'          => [
                               'select'         => 'callbacks:routerSelect',
                               'create'         => 'callbacks:routerCreate',
                               'delete'         => 'callbacks:routerDelete',
                               'toggle_enabled' => 'callbacks:routerToggleEnabled'
                           ]
                       ])
                   ]);

        $this->c('\std\ui resizable:bind', [
            'selector'      => $this->_selector('. > .routers'),
            'path'          => '>xhr:updateRoutersWidth',
            'pluginOptions' => [
                'handles' => 'e'
            ]
        ]);

        if ($selectedRouterId) {
            $v->assign('router', [
                'WIDTH'          => $this->s['router_width'],
                'SETTINGS'       => $this->c('routerSettings~:view', [
                    'context'   => $context,
                    'router_id' => $selectedRouterId,
                    'callbacks' => [
                        'update_name' => 'callbacks:routerUpdate',
                    ]
                ]),
                'COMPILE_BUTTON' => $this->c('\std\ui button:view', [
                    'path'    => 'router input:compile',
                    'data'    => [
                        'router_id' => $selectedRouterId
                    ],
                    'class'   => 'compile_button',
                    'content' => 'Скомпилировать'
                ]),
                'ROUTER'         => $this->c('router~:view', [
                    'context'           => $context,
                    'router_id'         => $selectedRouterId,
                    'selected_route_id' => $selectedRouteId,
                    'callbacks'         => [
                        'create_route'         => 'callbacks:routeCreate',
                        'delete_route'         => 'callbacks:routeDelete',
                        'select_route'         => 'callbacks:routeSelect',
                        'toggle_route_listen'  => 'callbacks:routeToggleListen',
                        'toggle_route_enabled' => 'callbacks:routeToggleEnabled'
                    ]
                ])
            ]);

            $this->c('\std\ui resizable:bind', [
                'selector'      => $this->_selector('. > .router'),
                'path'          => '>xhr:updateRouterWidth',
                'pluginOptions' => [
                    'handles' => 'e'
                ]
            ]);
        }

        if ($selectedRouteId) {
            $routeView = $this->c('route~:view', [
                'context'   => $context,
                'route_id'  => $selectedRouteId,
                'callbacks' => [
                    'update_name'     => 'callbacks:routeNameUpdate',
                    'update_pattern'  => 'callbacks:routeUpdate',
                    'update_type'     => 'callbacks:routeUpdate',
                    'set_target_type' => 'callbacks:routeUpdate'
                ]
            ]);

            if ($routeView) {
                $v->assign('route', [
                    'CONTENT' => $routeView
                ]);
            }
        }

        $this->css(':\css\std~');

        $this->c('\std\ui\dialogs~:addContainer:ewma/routers');

        $this->app->html->setFavicon(abs_url('-/ewma/favicons/dev_routers.png'));

        return $v;
    }
}
