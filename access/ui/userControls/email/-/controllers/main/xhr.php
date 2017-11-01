<?php namespace ewma\access\ui\userControls\email\controllers\main;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    public function update()
    {
        if ($user = $this->unxpackModel('user')) {
            $txt = \std\ui\Txt::value($this);

            $userWithThisEmail = \ewma\models\access\User::where('id', '!=', $user->id)->where('email', $txt->value)->first();

            if ($txt->value && $userWithThisEmail) {
                $txt->response($user->email);
            } else {
                $user->email = $txt->value;
                $user->save();

                $txt->response();

                $this->e('ewma/access/users/update/email')->trigger(['user' => $user]);
            }
        }
    }
}
