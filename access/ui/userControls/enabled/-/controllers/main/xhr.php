<?php namespace ewma\access\ui\userControls\enabled\controllers\main;

class Xhr extends \Controller
{
    public $allow = self::XHR;


    public function toggle()
    {
        if ($user = $this->unxpackModel('user')) {
            $user->enabled = !$user->enabled;
            $user->save();

            $this->e('ewma/access/users/update/enabled')->trigger(['user' => $user]);
        }
    }
}
