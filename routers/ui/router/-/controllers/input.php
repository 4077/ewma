<?php namespace ewma\routers\ui\router\controllers;

use ewma\routers\Routes;

class Input extends \Controller
{
    public $allow = self::XHR;

    private function performCallback($event, $routerId, $routeId)
    {
        if ($context = $this->data('context')) {
            if ($callback = $this->d('contexts:callbacks/' . $event . '|' . $context)) {
                $this->_call($callback)
                    ->data('context', $context)
                    ->data('router_id', $routerId)
                    ->data('route_id', $routeId)
                    ->perform();
            }
        }
    }

    public function compile()
    {
        $this->c('^:compileRouter', false, 'router_id');
    }

    public function create()
    {
        if ($route = Routes::create($this->data('route_id'))) {
            $this->performCallback('create_route', $this->data['router_id'], $route->id);
        }
    }

    public function duplicate()
    {
        if ($route = \ewma\routers\models\Route::find($this->data('route_id'))) {
            $routeOutputAssignment = Routes::getHandlersOutput($route->id);

            $newRouteOutputAssignment = \ewma\handlers\models\Assignment::create($routeOutputAssignment->toArray());

            $newRouteData = $route->toArray();
            $newRouteData['target_handlers_output_id'] = $newRouteOutputAssignment->id;

            $newRoute = \ewma\routers\models\Route::create($newRouteData);

            foreach ($routeOutputAssignment->nested()->orderBy('position')->get() as $nested) {
                \ewma\handlers\Assignments::paste($nested->id, $newRouteOutputAssignment->id);
            }

            $this->performCallback('create_route', $this->data['router_id'], $route->id);
        }
    }

//            $routeAssignmentsTree = \ewma\Data\Tree::get($routeOutputAssignment);
//
//            $newRouteOutputAssignment = $this->duplicateRecursion($routeAssignmentsTree, $routeOutputAssignment);
//    private function duplicateRecursion(\ewma\Data\Tree $tree, $assignment, $parentAssignment = null)
//    {
//        $newAssignmentData = $assignment->toArray();
//        if (null !== $parentAssignment) {
//            $newAssignmentData['parent_id'] = $parentAssignment->id;
//        }
//
//        $newAssignment = \ewma\handlers\models\Assignment::create($newAssignmentData);
//
//        $subnodes = $tree->getSubnodes($assignment->id);
//
//        foreach ($subnodes as $subnode) {
//            $this->duplicateRecursion($tree, $subnode, $newAssignment);
//        }
//
//        return $newAssignment;
//    }

    public function toggleListen()
    {
        Routes::toggleListen($this->data('route_id'));

        $this->performCallback('toggle_route_listen', $this->data['router_id'], $this->data['route_id']);
    }

    public function toggleEnabled()
    {
        Routes::toggleEnabled($this->data('route_id'));

        $this->performCallback('toggle_route_enabled', $this->data['router_id'], $this->data['route_id']);
    }

    public function delete()
    {
        if ($this->data('discarded')) {
            $this->c('\std\ui\dialogs~:close:deleteConfirm|ewma/routers');
        } else {
            if ($this->data('router_id') && $route = \ewma\routers\models\Route::find($this->data('route_id'))) {
                if ($this->data('confirmed')) {
                    Routes::delete($this->data['route_id']);

                    $this->c('\std\ui\dialogs~:close:deleteConfirm|ewma/routers');

                    $this->performCallback('delete_route', $this->data['router_id'], $this->data['route_id']);
                } else {
                    $this->c('\std\ui\dialogs~:open:deleteConfirm|ewma/routers', [
                        'path'          => '\std dialogs/confirm~:view',
                        'data'          => [
                            'confirm_call' => $this->_abs([':delete', $this->data]),
                            'discard_call' => $this->_abs([':delete', $this->data]),
                            'message'      => 'Удалить узел маршрута <b>' . ($route->name ? $route->name : '...') . '</b>?'
                        ],
                        'pluginOptions' => [
                            'resizable' => false
                        ]
                    ]);
                }
            }
        }
    }

    public function select()
    {
        if ($this->data('router_id') && $this->data('route_id')) {
            $this->performCallback('select_route', $this->data['router_id'], $this->data['route_id']);
        }
    }

    public function exchange()
    {
        if ($route = \ewma\routers\models\Route::find($this->data('route_id'))) {
            $routeNameBranch = trim_l_slash(a2p(\ewma\Data\Table\Transformer::getCells(\ewma\Data\Tree::getBranch($route), 'name')));

            $this->c('\std\ui\dialogs~:open:exchange|ewma/routers', [
                'default'             => [
                    'pluginOptions/width' => 500
                ],
                'path'                => '\std\data\exchange~:view|ewma/routers',
                'data'                => [
                    'target_name' => $routeNameBranch,
                    'import_call' => $this->_abs('^app/exchange:import', ['route' => pack_model($route)]),
                    'export_call' => $this->_abs('^app/exchange:export', ['route' => pack_model($route)])
                ],
                'pluginOptions/title' => 'routes'
            ]);
        }
    }
}
