<?php namespace ewma\Logs;

use ewma\App\App;
use ewma\Service\Service;

class Log extends Service
{
    protected $services = ['app'];

    /**
     * @var App
     */
    public $app = App::class;

    //
    //
    //

    private $cache;

    public function getCache()
    {
        if (null === $this->cache) {
            $this->cache = $this->app->cache->read('modules');
        }

        return $this->cache;
    }

    protected function boot()
    {

    }
}
