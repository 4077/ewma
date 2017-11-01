<?php namespace ewma\access\ui\userControls\login\controllers\main;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    public function update()
    {
        if ($user = $this->unxpackModel('user')) {
            $txt = \std\ui\Txt::value($this);

            $userWithThisLogin = \ewma\models\access\User::where('id', '!=', $user->id)->where('login', $txt->value)->first();

            if ($userWithThisLogin) {
                $txt->response($user->login);
            } else {
                $user->login = $txt->value;
                $user->save();

                $txt->response();

                $this->e('ewma/access/users/update/login')->trigger(['user' => $user]);
            }
        }
    }
}
