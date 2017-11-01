<?php namespace ewma\access\ui\userControls\password\controllers\main;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    public function changeDialog()
    {
        if ($user = $this->unxpackModel('user')) {
            $this->c('\std\ui\dialogs~:open:changePassword|ewma/access/users', [
                'path'          => '^ui/password~:view',
                'data'          => [
                    'user' => pack_model($user)
                ],
                'pluginOptions' => [
                    'title' => 'Пароль пользователя ' . ($user->login ? $user->login : '...')
                ]
            ]);

            $this->e('ewma/access/users/delete:changePasswordDialogClose', ['user_id' => $user->id])->rebind('\std\ui\dialogs~:close:changePassword|ewma/access/users');
            $this->e('ewma/access/users/update/password', ['user_id' => $user->id])->rebind('\std\ui\dialogs~:close:changePassword|ewma/access/users');
        }
    }
}
