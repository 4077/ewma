<?php namespace ewma\access\ui\userPermissions\controllers\main;

class Permissions extends \Controller
{
    private $user;

    private $module;

    public function __create()
    {
        $s = $this->s('~');

        $this->user = $this->unpackModel('user');
        $this->module = $this->app->modules->getByPath($s['selected_module_path']);
    }

    public function reload()
    {
        $this->jquery()->replace($this->view());
    }

    public function view()
    {
        $v = $this->v();

        $rootNode = $this->getRootNode();

        $this->renderTreeInfo($rootNode);

        $fullAccessEnabled = in_array($rootNode->id, $this->enabledIds);

        $fullAccessLink = $this->user->permissions->find($rootNode);

        $fullAccessMode = false;
        if ($fullAccessLink) {
            $fullAccessMode = $fullAccessLink->pivot->mode;
        }

        $v->assign([
                       'MODULE_SELECT'             => $this->moduleSelectView(),
                       'FULL_ACCESS_ENABLED_CLASS' => $fullAccessEnabled ? 'full_access_enabled' : '',
                       'FULL_ACCESS_MERGE_BUTTON'  => $this->c('\std\ui button:view', [
                           'path'    => '>xhr:toggleFullAccess:merge',
                           'data'    => [
                               'module_namespace' => $this->module->namespace,
                               'user'             => xpack_model($this->user)
                           ],
                           'class'   => 'full_access merge button ' . ($fullAccessMode == 'MERGE' ? 'pressed' : ''),
                           'content' => '<div class="icon"></div>'
                       ]),
                       'FULL_ACCESS_DIFF_BUTTON'   => $this->c('\std\ui button:view', [
                           'path'    => '>xhr:toggleFullAccess:diff',
                           'data'    => [
                               'module_namespace' => $this->module->namespace,
                               'user'             => xpack_model($this->user)
                           ],
                           'class'   => 'full_access diff button ' . ($fullAccessMode == 'DIFF' ? 'pressed' : ''),
                           'content' => '<div class="icon"></div>'
                       ]),
                       'TREE'                      => $this->treeView($rootNode)
                   ]);

        $this->css(':\css\std~, \jquery\ui icons');

        $eventFilter = ['user_id' => $this->user->id];
        $eventData = ['user' => pack_model($this->user)];

        $this->e('ewma/access/users/update/groups', $eventFilter)->rebind(':reload', $eventData);
        $this->e('ewma/access/users/update/permissions', $eventFilter)->rebind(':reload', $eventData);

        $this->e('ewma/access/permissions/create')->rebind(':reload', $eventData);
        $this->e('ewma/access/permissions/delete')->rebind(':reload', $eventData);

        return $v;
    }

    private function moduleSelectView()
    {
        $rootPermissions = \ewma\models\access\Permission::where('parent_id', 0)->get();

        $items = [];
        foreach ($rootPermissions as $permission) {
            $items[$permission->module_namespace] = $permission->name ? $permission->name : $permission->module_namespace;
        }

        return $this->c('\std\ui select:view', [
            'path'     => '>xhr:selectModule',
            'data'     => [
                'user' => xpack_model($this->user)
            ],
            'items'    => $items,
            'selected' => $this->module->namespace
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
                'user'                   => pack_model($this->user),
                'permission'             => '%model',
                'root_node_id'           => $rootNode->id,
                'enabled_ids'            => $this->enabledIds,
                'auto_enabled_ids'       => $this->autoEnabledIds,
                'has_nested_enabled_ids' => $this->hasNestedEnabledIds,
                'merge_ids'              => $this->mergeIds,
                'diff_ids'               => $this->diffIds
            ])
        ]);
    }

    private $enabledIds = [];

    private $mergeIds = [];

    private $diffIds = [];

    private function renderTreeInfo($rootNode)
    {
        $user = $this->user;

        $userGroupsIds = $user->groups()->get()->pluck('id')->all();

        $registeredUserGroup = \ewma\models\access\Group::where('system_type', 'REGISTERED')->first();
        if ($registeredUserGroup) {
            merge($userGroupsIds, $registeredUserGroup->id);
        }

        $userPermissionsByGroups = \ewma\models\access\Permission::where('module_namespace', $this->module->namespace)
            ->whereHas('groups', function ($query) use ($userGroupsIds) {
                $query->whereIn('id', $userGroupsIds);
            })->get()->pluck('id')->all();

        merge($this->enabledIds, $userPermissionsByGroups);

        $userPermissions = [];
        $this->user->permissions()->get()->each(function ($permission) use (&$userPermissions) {
            $userPermissions[$permission->id] = $permission->pivot->mode;
        });

        foreach ($userPermissions as $permissionId => $mode) {
            if ($mode == 'MERGE') {
                merge($this->mergeIds, $permissionId);
                merge($this->enabledIds, $permissionId);
            }

            if ($mode == 'DIFF') {
                merge($this->diffIds, $permissionId);
                diff($this->enabledIds, $permissionId);
            }
        }

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
