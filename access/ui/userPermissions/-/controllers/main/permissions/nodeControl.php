<?php namespace ewma\access\ui\userPermissions\controllers\main\permissions;

class NodeControl extends \Controller
{
    private $user;

    private $permission;

    private $viewInstance;

    public function __create()
    {
        $this->user = $this->unpackModel('user');
        $this->permission = $this->data['permission'];

        $this->viewInstance = path($this->_instance(), $this->permission->id);
    }

    public function view()
    {
        $v = $this->v('|' . $this->viewInstance);

        $user = $this->user;
        $permission = $this->permission;

        $isRootNode = $this->data['root_node_id'] == $permission->id;

        if ($isRootNode) {
            $name = $permission->module_namespace;
        } else {
            $name = $permission->name;
        }

        $mode = in_array($permission->id, $this->data['merge_ids'])
            ? 'merge'
            : (in_array($permission->id, $this->data['diff_ids']) ? 'diff' : '');

        $v->assign([
                       'NAME'                     => $name,// . ' ' . $mode . ' #' . $permission->id,
                       'ROOT_CLASS'               => $isRootNode ? 'root' : '',
                       'MODE_CLASS'               => $mode,
                       'ENABLED_CLASS'            => in_array($permission->id, $this->data['enabled_ids']) ? 'enabled' : '',
                       'AUTO_ENABLED_CLASS'       => in_array($permission->id, $this->data['auto_enabled_ids']) ? 'auto_enabled' : '',
                       'HAS_NESTED_ENABLED_CLASS' => in_array($permission->id, $this->data['has_nested_enabled_ids']) ? 'has_nested_enabled' : ''
                   ]);

        if (!$isRootNode) {
            $v->assign('buttons', [
                'MERGE' => $this->c('\std\ui button:view', [
                    'path'    => '>xhr:toggle:merge',
                    'data'    => [
                        'user'       => xpack_model($user),
                        'permission' => xpack_model($permission)
                    ],
                    'class'   => 'merge button',
                    'content' => '<div class="icon"></div>'
                ]),
                'DIFF'  => $this->c('\std\ui button:view', [
                    'path'    => '>xhr:toggle:diff',
                    'data'    => [
                        'user'       => xpack_model($user),
                        'permission' => xpack_model($permission)
                    ],
                    'class'   => 'diff button',
                    'content' => '<div class="icon"></div>'
                ])
            ]);
        }

        $this->css(':\jquery\ui icons');

        return $v;
    }
}
