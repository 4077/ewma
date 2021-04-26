<?php namespace ewma\Access;

use ewma\App\App;
use ewma\Service\Service;

class Access extends Service
{
    protected $services = ['app', 'auth'];

    /**
     * @var App
     */
    public $app = App::class;

    /**
     * @var Auth
     */
    public $auth = Auth::class;

    private $user;

    public function setUser(\ewma\access\models\User $user)
    {
        $this->user = new User($user);

        $this->updateUserSession($user->session_key);
    }

    public function updateUserSession($sessionKey)
    {
        if ($sessionKey) {
            if ($someSessionRecord = \ewma\models\Session::where('key', $sessionKey)->first()) {
                $sessionPublicKey = $someSessionRecord->public_key;
            } else {
                $sessionPublicKey = $this->generateSessionPublicKey();
            }
        } else {
            $sessionKey = $this->generateSessionKey();
            $sessionPublicKey = $this->generateSessionPublicKey();
        }

        $this->getUser()->setSessionKey($sessionKey);

        $this->app->session->setKey($sessionKey);
        $this->app->session->setPublicKey($sessionPublicKey);

        $this->app->session->setTimeout($this->app->getConfig('access/guest_session_timeout')); // todo сделать чтобы этот таймаут работал
    }

    public function createGuestSession()
    {
        $sessionKey = $this->generateSessionKey();
        $sessionPublicKey = $this->generateSessionPublicKey();

        $this->app->session->setKey($sessionKey);
        $this->app->session->setPublicKey($sessionPublicKey);

        $this->app->session->setTimeout($this->app->getConfig('access/guest_session_timeout'));

        $cookiePrefix = $this->app->getConfig('cookies_prefix');
        $sessionKeyCookieName = $cookiePrefix . 'k';

        $this->app->rootController->cookie_(
            $sessionKeyCookieName,
            $sessionKey,
            $this->app->getConfig('access/guest_session_timeout')
        );
    }

    private function generateSessionKey()
    {
        do {
            $key = k(32);
        } while (\ewma\models\Session::where('key', $key)->first());

        return $key;
    }

    private function generateSessionPublicKey()
    {
        do {
            $key = k(16);
        } while (\ewma\models\Session::where('public_key', $key)->first());

        return $key;
    }

    public function unsetUser()
    {
        $this->user = null;
    }

    /**
     * @return User
     */
    public function getUser(\ewma\access\models\User $user = null)
    {
        if (null !== $user) {
            return $this->getOtherUser($user);
        } else {
            return $this->user;
        }
    }

    private $users = [];

    /**
     * @return User
     */
    private function getOtherUser(\ewma\access\models\User $user)
    {
        if (!isset($this->users[$user->id])) {
            $this->users[$user->id] = new User($user);
        }

        return $this->users[$user->id];
    }
}
