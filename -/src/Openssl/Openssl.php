<?php namespace ewma\Openssl;

use ewma\App\App;
use ewma\Service\Service;

class Openssl extends Service
{
    protected $services = ['app'];

    /**
     * @var App
     */
    public $app = App::class;

    private $cipher;

    private $ivlen;

    private $key;

    private $iv;

    public function boot()
    {
        $this->cipher = $this->app->getConfig('openssl/cipher');
        $this->ivlen = $this->app->getConfig('openssl/ivlen');

        $this->key = read(abs_path('config/.openssl/key'));
        $this->iv = read(abs_path('config/.openssl/iv'));
    }

    public function encrypt($string, $raw = false)
    {
        return openssl_encrypt($string, $this->cipher, $this->key, $raw ? OPENSSL_RAW_DATA : 0, $this->iv);
    }

    public function decrypt($string, $raw = false)
    {
        return openssl_decrypt($string, $this->cipher, $this->key, $raw ? OPENSSL_RAW_DATA : 0, $this->iv);
    }
}
