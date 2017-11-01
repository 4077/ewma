<?php namespace ewma\access\ui\userControls\permissions\controllers\main;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    public function permissionsDialog()
    {
        if ($user = $this->unxpackModel('user')) {
            $this->c('\std\ui\dialogs~:open:userPermissions|ewma/access/users', [
                'path'          => '^ui/userPermissions~:view',
                'data'          => [
                    'user' => pack_model($user)
                ],
                'pluginOptions' => [
                    'title' => 'Разрешения пользователя ' . ($user->login ? $user->login : '...')
                ]
            ]);

            $this->e('ewma/access/users/delete:userPermissionsDialogClose', ['user_id' => $user->id])->rebind('\std\ui\dialogs~:close:userPermissions|ewma/access/users');
        }
    }
}
