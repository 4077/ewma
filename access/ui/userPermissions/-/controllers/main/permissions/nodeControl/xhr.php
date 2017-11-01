<?php namespace ewma\access\ui\userPermissions\controllers\main\permissions\nodeControl;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    public function toggle($mode)
    {
        $user = $this->unxpackModel('user');
        $permission = $this->unxpackModel('permission');

        if ($user && $permission && in($mode, 'merge, diff')) {
            \ewma\access\Users::togglePermissionLink($user, $permission, $mode);

            $this->e('ewma/access/users/update/permissions', ['user_id' => $user->id])->trigger(['user' => $user]);
        }
    }
}
