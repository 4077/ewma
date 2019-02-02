<?php namespace ewma\Html;

use ewma\App\App;
use ewma\Controllers\Controller;
use ewma\Service\Service;

class Meta extends Service
{
    protected $services = ['app'];

    /**
     * @var App
     */
    public $app = App::class;

    /**
     * @var $controller \ewma\controllers\Html
     */
    private $controller; // не используется

    public function boot()
    {
        $this->controller = $this->app->c('\ewma~html/meta');
    }

    //
    //
    //

    private $tags = [];

    public function set($name, $content, $httpEquiv = false)
    {
        if ($this->app->mode == App::REQUEST_MODE_ROUTE) {
            $this->tags[$name] = [
                'content' => str_replace('\"', '"', $content),
                'equiv'   => $httpEquiv
            ];
        }

        if ($this->app->mode == App::REQUEST_MODE_XHR) {
            appc()->jquery("meta[name='" . $name . "']")->attr("content", $content);
            appc()->jquery("meta[http-equiv='" . $name . "']")->attr("content", $content);
        }

        return $this;
    }

    public function get($name = null)
    {
        if (null === $name) {
            return $this->tags;
        } else {
            if (isset($this->tags[$name])) {
                return $this->tags[$name];
            }
        }
    }

    private $instance;

    public function setInstance($instance)
    {
        $this->instance = $instance;

        return $this;
    }

    public function getInstance()
    {
        return $this->instance;
    }
}
