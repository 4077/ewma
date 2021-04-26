<?php namespace ewma\Request;

use ewma\App\App;
use ewma\Service\Service;
use ewma\Controllers\Controller;
use ewma\Views\View;

class Request extends Service
{
    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $proxy;

    protected function boot()
    {
        $this->proxy = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
    }

    protected $services = ['app'];

    /**
     * @var App
     */
    public $app = App::class;

    public function handleCliRequest($call)
    {
        $this->app->mode = App::REQUEST_MODE_CLI;

        $this->renderLogString($call);

        $response = $this->app->requestHandlerController->_call($call)->perform();

        $this->app->response->sendAppResponse($response);
    }

    public function handle()
    {
        if ($this->proxy->isXmlHttpRequest()) {
            $call = $this->proxy->request->get('call') or
            $call = $this->proxy->query->get('call');

            $tab = $this->proxy->request->get('tab') or
            $tab = $this->proxy->query->get('tab');

            $this->handleXmlHttpRequest(json_decode($call, true), $tab);
        } else {
            $this->handleRouteRequest();
        }
    }

    private function handleXmlHttpRequest($call, $tab)
    {
        $this->app->mode = App::REQUEST_MODE_XHR;
        $this->app->tab = $tab;

        if ($user = $this->app->access->getUser() and $user->isSuperuser()) {
            $whoops = new \Whoops\Run;
            $whoops->prependHandler(new \Whoops\Handler\JsonResponseHandler());
            $whoops->register();
        }

        $this->renderLogString($call);

        if ('#' == substr($call[0], 0, 1)) {
            $handlerSource = substr($call[0], 1);

            handlers()->render($handlerSource, $call[1]);
        } else {
            $this->app->requestHandlerController->_call($call)->perform(Controller::XHR);
        }

        $this->app->response->sendAppResponse();
    }

    private function handleRouteRequest()
    {
        $this->app->mode = App::REQUEST_MODE_ROUTE;

        if ($user = $this->app->access->getUser() and $user->isSuperuser()) {
            $whoops = new \Whoops\Run;
            $handler = new \Whoops\Handler\PrettyPageHandler;

            $handler->setEditor(function ($file, $line) {
                return "phpstorm://open/?file=$file&line=$line";
            });

            $whoops->prependHandler($handler);
            $whoops->register();
        }

        $this->setRoute();
        $this->setData();

        $this->renderLogString();

        $response = $this->app->ewmaController->c('~request:handle');

        if ($response instanceof View) {
            $response = $response->render();
        }

        $this->app->response->sendAppResponse($response);
    }

    public $data = [];

    private function setRoute()
    {
        $requestPath = rawurldecode($this->proxy->getPathInfo());

        $this->app->route = trim_slashes($requestPath);
    }

    private function setData()
    {
        $requestData = $this->proxy->query->all();

        foreach ($requestData as $path => $value) {
            $this->data($path, $value);
        }
    }

    public $logString;

    private function renderLogString($data = false)
    {
        $clientName = $this->app->rootController->_user('login') or
        $clientName = $this->app->session->getPublicKey();

        $output = [
            $this->app->host,
            $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '0.0.0.0',
            '[' . $clientName . ']'
        ];

        if ($this->app->mode == App::REQUEST_MODE_XHR) {
            $output[] = 'XHR: ' . $data[0] . ' ' . j_($data[1]);
        }

        if ($this->app->mode == App::REQUEST_MODE_CLI) {
            $output[] = 'CLI: ' . $data[0] . ' ' . j_($data[1]);
        }

        if ($this->app->mode == App::REQUEST_MODE_ROUTE) {
            $output[] = 'ROUTE: ' . $this->app->route . ($this->data ? ' DATA: ' . j_($this->data) : '');
        }

        $this->logString = implode(' ', $output);
    }

    /**
     * @param bool|false $path
     * @param null       $value
     *
     * @return $this|null
     */
    public function data($path = false, $value = null)
    {
        if (null !== $value) {
            ap($this->data, $path, $value);

            return $this;
        } else {
            return ap($this->data, $path);
        }
    }
}
