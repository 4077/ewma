<?php namespace ewma\Html;

use ewma\App\App;
use ewma\Controllers\Controller;
use ewma\Service\Service;

class Html extends Service
{
    protected $services = ['app', 'meta'];

    /**
     * @var App
     */
    public $app = App::class;

    /**
     * @var \ewma\Html\Meta
     */
    public $meta = \ewma\Html\Meta::class;

    /**
     * @var $controller \ewma\controllers\Html
     */
    private $controller;

    public function boot()
    {
        $this->controller = $this->app->c('\ewma html');
    }

    //
    //
    //

    public function view()
    {
        return $this->controller->view();
    }

    // title

    private $title;

    public function setTitle($value)
    {
        if ($this->app->mode == \ewma\App\App::REQUEST_MODE_ROUTE) {
            $this->title = str_replace('\"', '"', $value);
        }

        if ($this->app->mode == \ewma\App\App::REQUEST_MODE_XHR) {
            $this->controller->jquery("title")->html($value);
        }

        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    // favicon

    private $favicon;

    public function setFavicon($href)
    {
        $this->favicon = $href;

        return $this;
    }

    public function getFavicon()
    {
        return $this->favicon;
    }

    // content

    private $content;

    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }

    // containers

    private $containers = [];

    public function addContainer($name = '', $content = '')
    {
        if ($this->app->mode == \ewma\App\App::REQUEST_MODE_ROUTE) {
            if (!isset($this->containers[$name])) {
                $this->containers[$name] = $this->containerView($name, $content);
            }
        }

        if ($this->app->mode == \ewma\App\App::REQUEST_MODE_XHR) {
            $this->controller->widget(":body", "addContainer", $name, $this->containerView($name, $content));
        }

        return $this;
    }

    public function replaceContainer($name = '', $content = '')
    {
        if ($this->app->mode == \ewma\App\App::REQUEST_MODE_ROUTE) {
            $this->containers[$name] = $this->containerView($name, $content);
        }

        if ($this->app->mode == \ewma\App\App::REQUEST_MODE_XHR) {
            $this->controller->widget(":body", "replaceContainer", $name, $this->containerView($name, $content));
        }

        return $this;
    }

    public function removeContainer($name = '')
    {
        if ($this->app->mode == \ewma\App\App::REQUEST_MODE_XHR) {
            $this->controller->widget(":body", "removeContainer", $name);
        }

        return $this;
    }

    public function getContainers()
    {
        return $this->containers;
    }

    private function containerView($name, $content)
    {
        return $this->controller->c('>container:view|' . $name, [
            'content' => $content
        ]);
    }

    public function getContainerSelector($name = '')
    {
        return $this->controller->_selector('>container:|' . $name);
    }

    // calls

    private $calls = [];

    public function addCall($name, $call)
    {
        $this->app->events->rebind('beforeRenderAppData', function () {
            foreach ($this->calls as $call) {
                $this->controller->_call($call)->perform();
            }
        });

        if (null === $name) {
            $this->calls[] = $call;
        } else {
            $this->calls[$name] = $call;
        }

        return $this;
    }
}
