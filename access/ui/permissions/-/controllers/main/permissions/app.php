<?php namespace ewma\access\ui\permissions\controllers\main\permissions;

class App extends \Controller
{
    public function treeQueryBuilder()
    {
        return \ewma\models\access\Permission::where('module_namespace', $this->data('module_namespace'))->orderBy('position');
    }

    public function moveCallback()
    {
        $permission = $this->data['permission'];

        \ewma\access\Permissions::updatePaths($permission);
    }

    public function addModule()
    {
        if ($module = $this->app->modules->getByPath($this->data('module_path'))) {
            $node = \ewma\models\access\Permission::where('module_namespace', $module->namespace)->where('parent_id', 0)->first();

            if (!$node) {
                \ewma\models\access\Permission::create([
                                                           'module_namespace' => $module->namespace,
                                                           'parent_id'        => 0
                                                       ]);
            }

            $this->s('~:selected_module_path', $module->path, RA);

            $this->c('~:reload');

            $this->c('\std\ui\dialogs~:close:permissionModuleSelector|ewma/access/permissions');
        }
    }
}
