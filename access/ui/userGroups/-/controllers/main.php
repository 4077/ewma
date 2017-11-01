<?php namespace ewma\access\ui\userGroups\controllers;

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

        $groups = \ewma\models\access\Group::where('system', false)->orderBy('position')->get();
        $userGroupsIds = $this->user->groups()->orderBy('position')->get()->pluck('id')->all();

        foreach ($groups as $group) {
            $v->assign('group', [
                'ID'             => $group->id,
                'NAME'           => $group->name,
                'SELECTED_CLASS' => in_array($group->id, $userGroupsIds) ? 'selected' : '',
            ]);

            $this->c('\std\ui button:bind', [
                'selector' => $this->_selector('|') . " .group[group_id='" . $group->id . "']",
                'path'     => '>xhr:toggleUserGroupLink',
                'data'     => [
                    'user'  => xpack_model($this->user),
                    'group' => xpack_model($group)
                ]
            ]);
        }

        $this->css();

        $this->e('ewma/access/users/update/groups')->rebind(':reload');

        return $v;
    }
}
