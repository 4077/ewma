<?php namespace ewma\access\ui\permissions\controllers\main\permissions;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    public function selectModule()
    {
        if ($module = $this->app->modules->getByNamespace($this->data('value'))) {
            $this->s('~:selected_module_path', $module->path, RA);

            $this->c('~:reload');
        }
    }

    public function add()
    {
        $this->c('\std\ui\dialogs~:open:permissionModuleSelector|ewma/access/permissions', [
            'path' => '\ewma\dev\ui\modulesTree~:view|' . $this->_nodeId(),
            'data' => [
                'callbacks' => [
                    'select' => $this->_abs('@app:addModule')
                ]
            ]
        ]);
    }

    public function delete()
    {
        if ($this->data('discarded')) {
            $this->c('\std\ui\dialogs~:close:deleteConfirm|ewma/access/permissions');
        } else {
            if ($module = $this->app->modules->getByNamespace($this->data('module_namespace'))) {
                if ($rootPermission = \ewma\models\access\Permission::where('module_namespace', $module->namespace)->where('parent_id', 0)->first()) {
                    if ($this->data('confirmed')) {
                        \ewma\access\Permissions::delete($rootPermission);

                        $this->s('~:selected_module_path', false, RR);

                        $this->c('\std\ui\dialogs~:close:deleteConfirm|ewma/access/permissions');
                        $this->e('ewma/access/permissions/delete')->trigger();
                    } else {
                        $this->c('\std\ui\dialogs~:open:deleteConfirm|ewma/access/permissions', [
                            'path'            => '\std dialogs/confirm~:view',
                            'data'            => [
                                'confirm_call' => $this->_abs([':delete|', ['module_namespace' => $this->data['module_namespace']]]),
                                'discard_call' => $this->_abs([':delete|', ['module_namespace' => $this->data['module_namespace']]]),
                                'message'      => 'Удалить все разрешения модуля <b>' . $module->namespace . '</b>?'
                            ],
                            'forgot_on_close' => true,
                            'pluginOptions'   => [
                                'resizable' => false
                            ]
                        ]);
                    }
                }
            }
        }
    }
}
