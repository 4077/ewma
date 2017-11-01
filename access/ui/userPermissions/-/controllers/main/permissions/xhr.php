<?php namespace ewma\access\ui\userPermissions\controllers\main\permissions;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    public function toggleSubnodes()
    {
        $s = &$this->s('<|');

        toggle($s['expand_nodes'], $this->data['node_id']);

        $this->c('<:reload|');
    }

    public function selectModule()
    {
        if ($module = $this->app->modules->getByNamespace($this->data('value'))) {
            $this->s('~:selected_module_path', $module->path, RA);

            $this->c('<:reload', [], 'user');
        }
    }

    public function toggleFullAccess($mode)
    {
        $user = $this->unxpackModel('user');
        $module = $this->app->modules->getByNamespace($this->data('module_namespace'));

        $rootPermission = \ewma\models\access\Permission::where('module_namespace', $module->namespace)->where('parent_id', 0)->first();

        if ($user && $module && $rootPermission && in($mode, 'merge, diff')) {
            \ewma\access\Users::togglePermissionLink($user, $rootPermission, $mode);

            $this->e('ewma/access/users/update/permissions', ['user_id' => $user->id])->trigger(['user' => $user]);
        }
    }
}
