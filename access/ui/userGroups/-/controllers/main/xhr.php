<?php namespace ewma\access\ui\userGroups\controllers\main;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    public function toggleUserGroupLink()
    {
        $user = $this->unpackModel('user');
        $group = $this->unpackModel('group');

        if ($user && $group) {
            \ewma\access\Users::toggleGroupLink($user, $group);

            $this->e('ewma/access/users/update/groups', ['user_id' => $user->id])->trigger(['user' => $user]);
        }
    }
}
