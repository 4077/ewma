<?php namespace ewma\access\ui\groups\controllers\main\permissions;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    public function selectModule()
    {
        if ($module = $this->app->modules->getByNamespace($this->data('value'))) {
            $this->s('~:selected_module_path', $module->path, RA);

            $this->c('<:reload');
        }
    }

    public function toggleFullAccess()
    {
        $group = $this->unxpackModel('group');
        $module = $this->app->modules->getByNamespace($this->data('module_namespace'));

        $rootPermission = \ewma\models\access\Permission::where('module_namespace', $module->namespace)->where('parent_id', 0)->first();

        if ($group && $module && $rootPermission) {
            \ewma\access\Groups::togglePermissionLink($module, $group, $rootPermission);

            $this->c('<:reload');
        }
    }
}
