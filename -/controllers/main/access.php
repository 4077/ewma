<?php namespace ewma\controllers\main;

class Access extends \Controller
{
    public function isUser()
    {
        return $this->_user() ? true : false;
    }

    public function isGuest()
    {
        return $this->_user() ? false : true;
    }

    public function has()
    {
        if ($user = $this->_user()) {
            $permissions = l2a($this->data('permissions'));

            diff($permissions, '');

            foreach ($permissions as $permission) {
                if ($user->hasPermission($this, $permission)) {
                    return true;
                }
            }
        }
    }

    public function hasPermission()
    {
        return $user = $this->_user() and $user->hasPermission($this, $this->data('path'));
    }

    public function otherUserHasPermission()
    {
        if ($userModel = \ewma\access\models\User::find($this->data('user_id'))) {
            return $user = $this->app->access->getUser($userModel) and $user->hasPermission($this, $this->data('path'));
        }
    }

    public function getUserModelById()
    {
        return \ewma\access\models\User::find($this->data('id'));
    }

    public function loginAs()
    {
        if ($user = $this->unpackModel('user')) {
            $cookiePrefix = $this->app->getConfig('cookies_prefix');
            $tokenCookieName = $cookiePrefix . 't';

            $this->app->access->setUser($user);

            $this->app->response->cookie(
                $tokenCookieName,
                $this->_user()->model->token,
                $this->app->getConfig('access/user_session_timeout'),
                '/'
            );
        }
    }

    public function logout()
    {
        $this->app->access->auth->logout();

        $this->app->response->redirect($this->data('redirect'));
    }

    public function createSystemGroups()
    {
        \ewma\access\Groups::createSystemGroups();
    }

    public function createUser()
    {
        $login = $this->data('login') or
        $login = $this->data('l');

        if ($login) {
            if ($user = \ewma\access\models\User::where('login', $login)->first()) {
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
                $user = \ewma\access\models\User::find($userId);
            }

            if (empty($user)) {
                $login = $this->data('login') or
                $login = $this->data('l') or
                $login = $this->data('u');

                if ($login) {
                    $user = \ewma\access\models\User::where('login', $login)->first();
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
