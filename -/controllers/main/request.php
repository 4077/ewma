<?php namespace ewma\controllers\main;

class Request extends \Controller
{
    public function handle()
    {
        $html = $this->app->html->up();

        $routers = $this->_config('routers');

        foreach ((array)$routers as $routerPath => $htmlWrapper) {
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
}
