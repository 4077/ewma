<?php namespace ewma\access\ui\userControls\groups\controllers\main;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    public function switcherDialog()
    {
        if ($user = $this->unxpackModel('user')) {
            $this->c('\std\ui\dialogs~:open:userGroupsSwitcher|ewma/access/users', [
                'path'          => '^ui/userGroups~:view',
                'data'          => [
                    'user' => pack_model($user)
                ],
                'pluginOptions' => [
                    'title' => 'Группы пользователя ' . ($user->login ? $user->login : '...')
                ]
            ]);

            $this->e('ewma/access/users/delete:userGroupsSwitcherDialogClose', ['user_id' => $user->id])->rebind('\std\ui\dialogs~:close:userGroupsSwitcher|ewma/access/users');
        }
    }
}
