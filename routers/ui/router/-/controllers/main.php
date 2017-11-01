<?php namespace ewma\routers\ui\router\controllers;

use ewma\routers\models\Router as RouterModel;
use ewma\routers\Routers;

class Main extends \Controller
{
    private $context;
    private $instance;
    private $router;
    private $contextData;

    public function __create()
    {
        if ($this->data('context') && $router = RouterModel::find($this->data('router_id'))) {
            $this->context = $this->data['context'];
            $this->instance = $this->data['router_id'];
            $this->router = $router;
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
        if ($this->instance) {
            $this->jquery(":|" . $this->instance)->replace($this->view());
        }
    }

    public function view()
    {
        if ($this->instance) {
            $v = $this->v('|' . $this->instance);

            $v->assign([
                           'ROUTES_TREE' => $this->routesTreeView($this->router)
                       ]);

            $this->css(':\jquery\ui icons, \css\std~');
            $this->widget(':|' . $this->router->id, [
                'routerId' => $this->router->id,
                'paths'    => [
                    'reload' => $this->_p('input:reload')
                ]
            ]);

            return $v;
        }
    }

    private function routesTreeView($router)
    {
        $rootRoute = Routers::getRootRoute($router);

        $this->renderTreeInfo($rootRoute);

        return $this->c('\std\ui\tree~:view|' . path($this->_nodeId(), $this->context, $router->id), [
            'default'          => [
                'query_builder' => '>app:treeQueryBuilder',
                'value_field'   => 'name'
            ],
            'root_node_id'     => $rootRoute->id,
            'expand'           => false,
            'selected_node_id' => $this->data('selected_route_id'),
            'movable'          => true,
            'sortable'         => true,
            'node_control'     => $this->_abs('>nodeControl:view', [
                'enabled_ids'  => $this->enabledIds,
                'route'        => '%model',
                'context'      => $this->context,
                'router_id'    => $router->id,
                'root_node_id' => $rootRoute->id
            ])
        ]);
    }

    private $enabledIds = [];

    private function renderTreeInfo($rootNode)
    {
        $tree = \ewma\Data\Tree::get($rootNode);

        $this->getTreeInfoRecursion($tree, $rootNode);
    }

    private $level = 0;

    private function getTreeInfoRecursion(\ewma\Data\Tree $tree, $node)
    {
        if ($node->enabled) {
            merge($this->enabledIds, $node->id);

            $subnodes = $tree->getSubnodes($node->id);

            foreach ($subnodes as $subnode) {
                $this->level++;

                $this->getTreeInfoRecursion($tree, $subnode);

                $this->level--;
            }
        }
    }
}
