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

        $response = $this->app->requestHandlerController->_call($call)->perform();

        $this->app->response->sendAppResponse($response);
    }

    public function handle()
    {
        if ($this->proxy->isXmlHttpRequest() && (
                $call = $this->proxy->request->get('call') or
                $call = $this->proxy->query->get('call')
            )
        ) {
            $this->handleXmlHttpRequest(json_decode($call, true));
        } else {
            $this->handleRouteRequest();
        }
    }

    private function handleXmlHttpRequest($call)
    {
        $this->app->mode = App::REQUEST_MODE_XHR;

        $this->app->ewmaController->c('logs~:write:requests', ['type' => 'xhr', 'call' => $call]);

        $this->app->requestHandlerController->_call($call)->perform(Controller::XHR);

        $this->app->response->sendAppResponse();
    }

    private function handleRouteRequest()
    {
        $this->app->mode = App::REQUEST_MODE_ROUTE;

        $this->setRoute();
        $this->setData();

        $this->app->ewmaController->c('logs~:write:requests', ['type' => 'route', 'route' => $this->app->route]);

        $response = $this->app->ewmaController->c('~:processRequest');

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
