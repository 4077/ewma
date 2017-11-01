<?php namespace ewma\access\controllers;

class Cli extends \Controller
{
    public function createSystemGroups()
    {
        \ewma\access\Groups::createSystemGroups();
    }

//    public function createSystemUsers()
//    {
//        \ewma\access\Users::createSystemUsers();
//    }

    public function createUser()
    {
        $login = $this->data('login') or
        $login = $this->data('l');

        if ($login) {
            if ($user = \ewma\models\access\User::where('login', $login)->first()) {
                return 'user already exists, id=' . $user->id;
            }

            $user = \ewma\access\Users::create($login);

            return 'user ' . $user->login . ' created, id=' . $user->id;
        }

        return 'not specified login';
    }

    public function setPass()
    {
        return $this->setUserPassword();
    }

    public function setUserPass()
    {
        return $this->setUserPassword();
    }

    public function setUserPassword()
    {
        if ($this->app->mode == \ewma\App\App::REQUEST_MODE_CLI) {
            $userId = $this->data('user_id') or
            $userId = $this->data('user') or
            $userId = $this->data('u');

            if (is_integer($userId)) {
                $user = \ewma\models\access\User::find($userId);
            }

            if (empty($user)) {
                $login = $this->data('login') or
                $login = $this->data('l') or
                $login = $this->data('u');

                if ($login) {
                    $user = \ewma\models\access\User::where('login', $login)->first();
                }
            }

            if (!empty($user)) {
                $pass = $this->data('pass') or
                $pass = $this->data('p');

                if ($pass) {
                    $this->app->access->getUser($user)->updatePass($pass);

                    return $user->login . ':' . $pass;
                }
            }
        }
    }
}
