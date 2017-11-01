<?php namespace ewma\access\ui\groups\controllers\main;

class Permissions extends \Controller
{
    private $group;

    private $module;

    public function __create()
    {
        $s = $this->s('~');

        $this->group = \ewma\models\access\Group::find($s['selected_group_id']);

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
            $rootNode = $this->getRootNode();

            $this->renderTreeInfo($rootNode);

            $fullAccessEnabled = in_array($rootNode->id, $this->enabledIds);

            $v->assign([
                           'FULL_ACCESS_ENABLED_CLASS' => $fullAccessEnabled ? 'full_access_enabled' : '',
                           'FULL_ACCESS_TOGGLE_BUTTON' => $this->c('\std\ui button:view', [
                               'path'    => '>xhr:toggleFullAccess',
                               'data'    => [
                                   'module_namespace' => $this->module->namespace,
                                   'group'            => xpack_model($this->group)
                               ],
                               'class'   => 'full_access_toggle_button ' . ($fullAccessEnabled ? 'pressed' : ''),
                               'attrs'   => [
                                   'title' => $fullAccessEnabled ? 'Выключить полный доступ' : 'Включить полный доступ'
                               ],
                               'content' => '<div class="icon"></div>'
                           ]),
                           'TREE'                      => $this->treeView($rootNode)
                       ]);
        }

        $this->css(':\css\std~, \jquery\ui icons');

        $this->e('ewma/access/groups/update/name', ['group_id' => $this->group->id])->rebind(':reload');
        $this->e('ewma/access/permissions/create')->rebind(':reload');

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
            $items[$permission->module_namespace] = $permission->name ? $permission->name : $permission->module_namespace;
        }

        return $this->c('\std\ui select:view', [
            'path'     => '>xhr:selectModule',
            'items'    => $items,
            'selected' => $selected
        ]);
    }

    private function treeView($rootNode)
    {
        return $this->c('\std\ui\tree~:view|' . $this->_nodeId(), [
            'default'           => [
                'query_builder' => '>app:treeQueryBuilder'
            ],
            'root_node_id'      => $rootNode->id,
            'root_node_visible' => false,
            'node_control'      => $this->_abs('>nodeControl:view', [
                'group'                  => pack_model($this->group),
                'permission'             => '%model',
                'root_node_id'           => $rootNode->id,
                'enabled_ids'            => $this->enabledIds,
                'auto_enabled_ids'       => $this->autoEnabledIds,
                'has_nested_enabled_ids' => $this->hasNestedEnabledIds
            ])
        ]);
    }

    private $enabledIds = [];

    private function renderTreeInfo($rootNode)
    {
        $this->enabledIds = $this->group->permissions()->get()->pluck('id')->all();

        $this->treeInfoBranch[] = $rootNode->id;

        $tree = \ewma\Data\Tree::get(
            \ewma\models\access\Permission::where('module_namespace', $this->module->namespace)
        );

        $this->getTreeInfoRecursion($tree, $rootNode);
    }

    private $treeInfoBranch = [];

    private $hasNestedEnabledIds = [];

    private $autoEnabledIds = [];

    private $level = 0;

    private function getTreeInfoRecursion(\ewma\Data\Tree $tree, $node)
    {
        if (array_intersect($this->treeInfoBranch, $this->enabledIds)) {
            merge($this->autoEnabledIds, $node->id);
        }

        if (in_array($node->id, $this->enabledIds)) {
            merge($this->hasNestedEnabledIds, array_slice($this->treeInfoBranch, 0, -1));
        }

        $subnodes = $tree->getSubnodes($node->id);

        foreach ($subnodes as $subnode) {
            $this->treeInfoBranch[] = $subnode->id;
            $this->level++;

            $this->getTreeInfoRecursion($tree, $subnode);

            array_pop($this->treeInfoBranch);
            $this->level--;
        }
    }

    private function getRootNode()
    {
        $node = \ewma\models\access\Permission::where('module_namespace', $this->module->namespace)->where('parent_id', 0)->first();

        if (!$node) {
            $node = \ewma\models\access\Permission::create([
                                                               'parent_id'        => 0,
                                                               'module_namespace' => $this->module->namespace
                                                           ]);
        }

        return $node;
    }
}
