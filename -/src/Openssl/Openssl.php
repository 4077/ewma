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

//    public function encrypt($string)
//    {
//        $raw = openssl_encrypt($string, $this->cipher, $this->key, OPENSSL_RAW_DATA, $this->iv);
//        $hmac = hash_hmac('sha256', $raw, $this->key, $as_binary = true);
//        $output = base64_encode($this->iv . $hmac . $raw);
//
//        return $output;
//    }
//
//    public function decrypt($string)
//    {
//        $rawInput = base64_decode($string);
//
//        $hmac = substr($rawInput, $this->ivlen, $sha2len = 32);
//        $raw = substr($rawInput, $this->ivlen + $sha2len);
//
//        $calcmac = hash_hmac('sha256', $string, $this->key, $as_binary = true);
//
////        if (hash_equals($hmac, $calcmac)) {
//            $output = openssl_decrypt($raw, $this->cipher, $this->key, OPENSSL_RAW_DATA, $this->iv);
//
//            return $output;
////        }
//    }


//    PHP >=7.1
//
    public function encrypt($string)
    {
        return openssl_encrypt($string, $this->cipher, $this->key, 0, $this->iv, $tag);
    }

    public function decrypt($string)
    {
        return openssl_decrypt($string, $this->cipher, $this->key, 0, $this->iv);
    }
}
