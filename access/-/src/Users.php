<?php namespace ewma\access;

class Users
{
//    public static function createSystemUsers()
//    {
//        if (!\ewma\models\access\User::where('system', true)->where('system_type', 'GUEST')->first()) {
//            \ewma\models\access\User::create([
//                                                 'system'      => true,
//                                                 'system_type' => 'NOBODY',
//                                                 'login'       => 'nobody'
//                                             ]);
//        }
//    }

    public static function create($login = '')
    {
        $user = \ewma\models\access\User::create([
                                                     'login' => $login
                                                 ]);

        return $user;
    }

    public static function toggleGroupLink($user, $group)
    {
        $link = $user->groups()->find($group->id);

        if ($link) {
            $user->groups()->detach([$group->id]);
        } else {
            $user->groups()->detach([$group->id]);
            $user->groups()->attach([$group->id]);
        }
    }

    public static function togglePermissionLink($user, $permission, $mode)
    {
        $link = $user->permissions->find($permission);

        if ($link) {
            $currentMode = $link->pivot->mode;

            if ($currentMode == strtoupper($mode)) {
                $user->permissions()->detach([$permission->id]);
            } else {
                $user->permissions()->detach([$permission->id]);
                $user->permissions()->attach([$permission->id], ['mode' => strtoupper($mode)]);
            }
        } else {
            $user->permissions()->detach([$permission->id]);
            $user->permissions()->attach([$permission->id], ['mode' => strtoupper($mode)]);
        }
    }
}
