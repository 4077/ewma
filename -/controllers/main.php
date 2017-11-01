<?php namespace ewma\controllers;

class Main extends \Controller
{
    public function processRequest()
    {
        $html = $this->app->html->up();

        $routers = $this->_config('routers');
        foreach ($routers as $routerPath => $htmlWrapper) {
            $routerResponse = $this->c($routerPath)->getResponse();

            if (null !== $routerResponse) {
                if ($htmlWrapper) {
                    $html->setContent($routerResponse);

                    return $html->view();
                } else {
                    return $routerResponse;
                }
            }
        }
    }

    public function bindEventDispatchers()
    {
        $eventDispatchers = $this->_config('eventDispatchers');
        foreach ($eventDispatchers as $eventDispatcher) {
            $this->app->c($eventDispatcher . ':bind');
        }
    }
}
