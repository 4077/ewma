<?php namespace ewma\access\ui\userControls\groups\controllers;

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

        $groups = $this->user->groups()->orderBy('position')->get();

        foreach ($groups as $group) {
            $v->assign('group', [
                'NAME' => $group->name
            ]);
        }

        $this->c('\std\ui button:bind', [
            'selector'                    => $this->_selector('|'),
            'path'                        => '>xhr:switcherDialog',
            'data'                        => [
                'user' => xpack_model($this->user)
            ],
            'eventTriggerClosestSelector' => '.cell'
        ]);

        $this->css();

        $this->e('ewma/access/users/update/groups')->rebind(':reload');

        return $v;
    }
}
