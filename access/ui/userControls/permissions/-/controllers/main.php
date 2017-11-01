<?php namespace ewma\access\ui\userControls\permissions\controllers;

class Main extends \Controller
{
    private $user;

    public function __create()
    {
        $this->user = $this->unpackModel('user');

        $this->instance_($this->user->id);
    }

    public function reload()
    {
        $this->jquery('|')->replace($this->view());
    }

    public function view()
    {
        $v = $this->v('|');

        $mergeCount = $this->user->permissions()->wherePivot('mode', 'MERGE')->count();
        $diffCount = $this->user->permissions()->wherePivot('mode', 'DIFF')->count();

        $v->assign([
                       'MERGE'       => $mergeCount,
                       'MERGE_CLASS' => $mergeCount ? '' : 'zero',
                       'DIFF'        => $diffCount,
                       'DIFF_CLASS'  => $diffCount ? '' : 'zero'
                   ]);

        $this->c('\std\ui button:bind', [
            'selector'                    => $this->_selector('|'),
            'path'                        => '>xhr:permissionsDialog',
            'data'                        => [
                'user' => xpack_model($this->user)
            ],
            'eventTriggerClosestSelector' => '.cell'
        ]);

        $this->css();

        $this->e('ewma/access/users/update/permissions')->rebind(':reload');

        return $v;
    }
}
