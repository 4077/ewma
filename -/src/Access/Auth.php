<?php namespace ewma\Access;

use ewma\App\App;
use ewma\Service\Service;

class Auth extends Service
{
    protected $services = ['app'];

    /**
     * @var App
     */
    public $app = App::class;

    public function process()
    {
        if (!$this->app->access->getUser()) {
            if (!$this->tryByUserToken()) {
                $this->tryBySessionKey();
            }
        }
    }

    private function tryByUserToken()
    {
        $cookiePrefix = $this->app->getConfig('cookies_prefix');
        $tokenCookieName = $cookiePrefix . 't';

        $userToken = $this->app->request->cookies->get($tokenCookieName);

        if ($userToken) {
            $user = \ewma\access\models\User::where('token', $userToken)->first();

            if ($user) {
                $this->app->access->setUser($user);

                $this->app->response->cookie(
                    $cookiePrefix . 't',
                    $userToken,
                    $this->app->getConfig('access/user_session_timeout'),
                    '/'
                );

                return true;
            } else {
                $this->app->response->cookie($cookiePrefix . 't');
            }
        }
    }

    private function tryBySessionKey()
    {
        $cookiePrefix = $this->app->getConfig('cookies_prefix');
        $sessionKeyCookieName = $cookiePrefix . 'k';

        $sessionKey = $this->app->request->cookies->get($sessionKeyCookieName);

        if ($sessionKey) {
            if (\ewma\models\Session::where('key', $sessionKey)->first()) {
                $this->app->session->setKey($sessionKey);

                $this->app->rootController->cookie_(
                    $sessionKeyCookieName,
                    $sessionKey,
                    $this->app->getConfig('access/user_session_timeout')
                );
            } else {
                $this->app->access->createGuestSession();
            }
        } else {
            $this->app->access->createGuestSession();
        }
    }

    public function generateUserToken()
    {
        do {
            $token = k(32);
        } while (\ewma\access\models\User::where('token', $token)->first());

        return $token;
    }

    public function login($uniqueFieldValue, $pass, $verify = true)
    {
        if ($this->app->access->getUser()) {
            return true;
        } else {
            $user = \ewma\access\models\User::getByUniqueField($uniqueFieldValue);

            if ($user) {
                $loginBySentPass = false;

                if ($verify) {
                    if ($user->sent_pass) {
                        $loginBySentPass = true;

                        $verified = $user->sent_pass == $pass;
                    } else {
                        $verified = password_verify($pass, $user->pass);
                    }
                } else {
                    $verified = true;
                }

                if ($verified) {
                    if (!$user->token) {
                        $user->token = $this->generateUserToken();
                        $user->save();
                    }

                    if ($loginBySentPass) {
                        $this->app->access->getUser($user)->updatePass($pass);

                        $user->sent_pass = '';
                        $user->save();
                    }

                    $cookiePrefix = $this->app->getConfig('cookies_prefix');
                    $tokenCookieName = $cookiePrefix . 't';

                    $this->app->response->cookie(
                        $tokenCookieName,
                        $user->token,
                        $this->app->getConfig('access/user_session_timeout')
                    );

                    $this->app->access->setUser($user);

                    return true;
                }
            }
        }
    }

    public function logout()
    {
        $this->app->access->unsetUser();

        $cookiePrefix = $this->app->getConfig('cookies_prefix');
        $tokenCookieName = $cookiePrefix . 't';

        $this->app->response->cookie($tokenCookieName);
    }

    public function logoutOnOtherDevices()
    {
        if ($user = $this->app->access->getUser()) {
            $newToken = $this->generateUserToken();

            $user->setToken($newToken);

            $cookiePrefix = $this->app->getConfig('cookies_prefix');
            $tokenCookieName = $cookiePrefix . 't';

            $this->app->response->cookie(
                $tokenCookieName,
                $newToken,
                $this->app->getConfig('access/user_session_timeout')
            );
        }
    }
}
