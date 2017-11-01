<?php namespace ewma\access\ui\groups\controllers\main\permissions;

class NodeControl extends \Controller
{
    private $permission;

    private $viewInstance;

    public function __create()
    {
        $this->permission = $this->data['permission'];

        $this->viewInstance = path($this->_instance(), $this->permission->id);
    }

    public function view()
    {
        $v = $this->v('|' . $this->viewInstance);

        $permission = $this->permission;

        $v->assign([
                       'NAME'                     => $permission->name,
                       'ENABLED_CLASS'            => in_array($permission->id, $this->data['enabled_ids']) ? 'enabled' : '',
                       'AUTO_ENABLED_CLASS'       => in_array($permission->id, $this->data['auto_enabled_ids']) ? 'auto_enabled' : '',
                       'HAS_NESTED_ENABLED_CLASS' => in_array($permission->id, $this->data['has_nested_enabled_ids']) ? 'has_nested_enabled' : ''
                   ]);

        $this->c('\std\ui button:bind', [
            'selector' => $this->_selector('|' . $this->viewInstance),
            'path'     => '>xhr:toggle',
            'data'     => [
                'permission' => xpack_model($permission)
            ]
        ]);

        $this->css(':\jquery\ui icons');

        return $v;
    }
}
