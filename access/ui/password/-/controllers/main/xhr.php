<?php namespace ewma\access\ui\password\controllers\main;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    public function update()
    {
        $s = &$this->s('~');

        $s['password'] = $this->data('value');
    }

    public function generate()
    {
        $s = &$this->s('~');

        $s['password'] = h(8);

        $this->c('<:reload', [], 'user');
    }

    public function set()
    {
        if ($user = $this->unpackModel('user')) {
            $s = &$this->s('~');

            if (strlen($s['password'])) {
                $this->app->access->getUser($user)->updatePass($s['password']);

                $s['password'] = '';

                $this->e('ewma/access/users/update/password', ['user_id' => $user->id])->trigger(['user' => $user]);
            }
        }
    }
}
