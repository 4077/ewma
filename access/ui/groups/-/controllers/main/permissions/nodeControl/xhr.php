<?php namespace ewma\access\ui\groups\controllers\main\permissions\nodeControl;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    public function toggle()
    {
        if ($permission = $this->unxpackModel('permission')) {
            $s = $this->s('~');

            $group = \ewma\models\access\Group::find($s['selected_group_id']);
            $module = $this->app->modules->getByPath($s['selected_module_path']);

            \ewma\access\Groups::togglePermissionLink($module, $group, $permission);

            $this->c('~permissions:reload');
        }
    }
}
