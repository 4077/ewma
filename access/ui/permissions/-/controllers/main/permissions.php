<?php namespace ewma\access\ui\permissions\controllers\main;

class Permissions extends \Controller
{
    private $module;

    public function __create()
    {
        $s = $this->s('~');

        if ($s['selected_module_path']) {
            $this->module = $this->app->modules->getByPath($s['selected_module_path']);
        }
    }

    public function reload()
    {
        $this->jquery()->replace($this->view());
    }

    public function view()
    {
        $v = $this->v();

        $v->assign([
                       'MODULE_SELECT' => $this->moduleSelectView()
                   ]);

        if ($this->module) {
            $v->assign([
                           'DELETE_BUTTON' => $this->c('\std\ui button:view', [
                               'path'    => '>xhr:delete',
                               'data'    => [
                                   'module_namespace' => $this->module->namespace
                               ],
                               'class'   => 'delete button',
                               'content' => '<div class="icon"></div>'
                           ]),
                           'ADD_BUTTON'    => $this->c('\std\ui button:view', [
                               'path'    => '>xhr:add',
                               'class'   => 'add button',
                               'content' => '<div class="icon"></div>'
                           ]),
                           'TREE'          => $this->treeView()
                       ]);
        }

        $this->css(':\css\std~, \jquery\ui icons');

        $eventData = [];

        $this->e('ewma/access/permissions/create')->rebind(':reload|', $eventData);
        $this->e('ewma/access/permissions/delete')->rebind(':reload|', $eventData);
        $this->e('ewma/access/permissions/import')->rebind(':reload|', $eventData);

        return $v;
    }

    private function moduleSelectView()
    {
        $rootPermissions = \ewma\models\access\Permission::where('parent_id', 0)->orderBy('module_namespace')->get();

        if (!$this->module) {
            $items[''] = '';
            $selected = '';
        } else {
            $items = [];
            $selected = $this->module->namespace;
        }

        foreach ($rootPermissions as $permission) {
            $items[$permission->module_namespace] = $permission->module_namespace;
        }

        return $this->c('\std\ui select:view', [
            'path'     => '>xhr:selectModule',
            'items'    => $items,
            'selected' => $selected
        ]);
    }

    private function treeView()
    {
        $rootNode = $this->getRootNode();

        return $this->c('\std\ui\tree~:view|' . $this->_nodeId(), [
            'default'       => [

            ],
            'query_builder' => [
                '>app:treeQueryBuilder',
                [
                    'module_namespace' => $this->module->namespace
                ]
            ],
            'node_control'  => [
                '>nodeControl:view',
                [
                    'node'         => '%model',
                    'root_node_id' => $rootNode->id,
                    'mode'         => 'both'
                ]
            ],
            'callbacks'     => [
                'move' => $this->_abs('>app:moveCallback', [
                    'permission' => '%source_model'
                ])
            ],
            'root_node_id'  => $rootNode->id,
            'value_field'   => 'name',
            'movable'       => true,
            'sortable'      => true,
            'expand'        => true
        ]);
    }

    private function getRootNode()
    {
        $node = \ewma\models\access\Permission::where('module_namespace', $this->module->namespace)->where('parent_id', 0)->first();

        if (!$node) {
            $node = \ewma\models\access\Permission::create([
                                                               'module_namespace' => $this->module->namespace,
                                                               'parent_id'        => 0
                                                           ]);
        }

        return $node;
    }
}
