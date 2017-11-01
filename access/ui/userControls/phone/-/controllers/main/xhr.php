<?php namespace ewma\access\ui\userControls\phone\controllers\main;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    public function update()
    {
        if ($user = $this->unxpackModel('user')) {
            $txt = \std\ui\Txt::value($this);

            $phone = \ewma\Data\Formats\Phone::parse($txt->value, 7);

            $userWithThisPhone = \ewma\models\access\User::where('id', '!=', $user->id)->where('phone', $phone)->first();

            if ($phone && $userWithThisPhone) {
                $txt->response($user->phone);
            } else {
                $user->phone = $phone;
                $user->save();

                $txt->response();

                $this->e('ewma/access/users/update/phone')->trigger(['user' => $user]);
            }
        }
    }
}
