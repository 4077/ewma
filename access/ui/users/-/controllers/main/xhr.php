<?php namespace ewma\access\ui\users\controllers\main;

class Xhr extends \Controller
{
    public $allow = self::ALL;

    public function create()
    {
        \ewma\access\Users::create();

        $this->e('ewma/access/users/create')->trigger();
    }

    public function delete()
    {
        if ($this->data('discarded')) {
            $this->c('\std\ui\dialogs~:close:deleteConfirm|ewma/access/users');
        } else {
            if ($user = $this->unxpackModel('user')) {
                if ($this->data('confirmed')) {
                    $user->delete();

                    $this->c('\std\ui\dialogs~:close:deleteConfirm|ewma/access/users');

                    $this->e('ewma/access/users/delete', ['user_id' => $user->id])->trigger(['user' => $user]);
                } else {
                    $this->c('\std\ui\dialogs~:open:deleteConfirm|ewma/access/users', [
                        'path'            => '\std dialogs/confirm~:view',
                        'data'            => [
                            'confirm_call' => $this->_abs([':delete', ['user' => $this->data['user']]]),
                            'discard_call' => $this->_abs([':delete', ['user' => $user->data['user']]]),
                            'message'      => 'Удалить пользователя <b>' . ($user->login ? $user->login : '...') . '</b>?'
                        ],
                        'forgot_on_close' => true,
                        'pluginOptions'   => [
                            'resizable' => 'false'
                        ]
                    ]);
                }
            }
        }
    }

    public function userDialog()
    {
        if ($user = $this->unxpackModel('user')) {
            $this->c('\std\ui\dialogs~:open:user|ewma/access/users', [
                'path'          => '^ui/user~:view',
                'data'          => [
                    'user' => pack_model($user)
                ],
                'pluginOptions' => [
                    'title' => 'Пользователь ' . $user->login
                ]
            ]);

            $this->e('ewma/access/users/delete:userDialogClose', ['user_id' => $user->id])->rebind('\std\ui\dialogs~:close:user|ewma/access/users');
        }
    }
}
