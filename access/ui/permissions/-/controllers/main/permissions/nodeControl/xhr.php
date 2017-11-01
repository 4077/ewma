<?php namespace ewma\access\ui\permissions\controllers\main\permissions\nodeControl;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    public function create()
    {
        if ($node = $this->unpackModel('node')) {
            $newNode = $node->create([
                                         'module_namespace' => $node->module_namespace,
                                         'name'             => '',
                                         'parent_id'        => $node->id
                                     ]);

            $this->e('ewma/access/permissions/create')->trigger(['permission' => $newNode]);
        }
    }

    public function delete()
    {
        if ($this->data('discarded')) {
            $this->c('\std\ui\dialogs~:close:deleteConfirm|ewma/access/permissions');
        } else {
            if ($node = $this->unpackModel('node')) {
                if ($this->data('confirmed')) {
                    \ewma\access\Permissions::delete($node);

                    $this->c('\std\ui\dialogs~:close:deleteConfirm|ewma/access/permissions');
                    $this->e('ewma/access/permissions/delete')->trigger();
                } else {
                    $this->c('\std\ui\dialogs~:open:deleteConfirm|ewma/access/permissions', [
                        'path'            => '\std dialogs/confirm~:view',
                        'data'            => [
                            'confirm_call' => $this->_abs([':delete|', ['node' => $this->data['node']]]),
                            'discard_call' => $this->_abs([':delete|', ['node' => $this->data['node']]]),
                            'message'      => 'Удалить разрешение <b>' . ($node->name ? $node->name : '...') . '</b>?'
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

    public function updateName()
    {
        if ($permission = $this->unpackModel('node')) {
            $txt = \std\ui\Txt::value($this);

            $permission->name = $txt->value;
            $permission->save();

//            if ($permission->parent_id == 0) {
//                $txt->response('[' . $permission->module_namespace . '] ' . ($permission->name ? $permission->name : '...'), $permission->name);
//            } else {
//                $txt->response();
//            }

            $txt->response();
        }
    }

    public function updatePathSegment()
    {
        if ($permission = $this->unpackModel('node')) {
            $txt = \std\ui\Txt::value($this);

            $permission->path_segment = $txt->value;
            $permission->save();

            \ewma\access\Permissions::updatePaths($permission);

            $txt->response();
        }
    }

    public function exchangeDialog()
    {
        if ($permission = $this->unpackModel('node')) {
            $this->c('\std\ui\dialogs~:open:exchange|ewma/access/permissions', [
                'default'             => [
                    'pluginOptions/width' => 500
                ],
                'path'                => '\std\data\exchange~:view|ewma/access/permissions',
                'data'                => [
                    'target_name' => '#' . $permission->module_namespace . ':' . $permission->path,
                    'import_call' => $this->_abs('app/exchange:import', ['permission' => pack_model($permission)]),
                    'export_call' => $this->_abs('app/exchange:export', ['permission' => pack_model($permission)])
                ],
                'pluginOptions/title' => 'permissions'
            ]);
        }
    }
}
