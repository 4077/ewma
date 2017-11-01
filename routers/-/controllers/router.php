<?php namespace ewma\routers\controllers;

use ewma\Interfaces\RouterInterface;
use ewma\Route\ResolvedRoute;

class Router extends \Controller implements RouterInterface
{
    public function getResponse()
    {
        if ($routers = aread(abs_path('cache/routers/routers.php'))) {
            foreach ($routers as $routerId) {
                if (null !== $response = $this->getRouterResponse($routerId)) {
                    return $response;
                }
            }
        }
    }

    private function getRouterResponse($routerId)
    {
        if ($router = aread(abs_path('cache/routers/' . $routerId . '.php'))) {
            foreach ($router as $route) {
                if (null !== $response = $this->getRouteResponse($route)) {
                    return $response;
                }
            }
        }
    }

    private function getRouteResponse($route)
    {
        if ($route['listen']) {
            $response = null;
            $resolvedRoute = $this->route($route['pattern']);

            if ($resolvedRoute instanceof ResolvedRoute) {
                if ($route['response_wrapper'] == 'EWMA_HTML') {
                    $ewmaHtml = $this->app->html->up();
                }

                if ($route['type'] == 'METHOD' && $route['path']) {
                    $resolvedRoute->to($route['path'], $route['data']);

                    $response = $this->routeResponse();
                }

                if ($route['type'] == 'HANDLERS_OUTPUT' && $route['handlers_output_id']) {
                    $resolvedRoute->to(':processHandlersOutput:' . $route['handlers_output_id'], [
                        'route_id'   => $route['id'],
                        'route_name' => $route['name'],
                        'route'      => $this->app->route
                    ]);

                    $response = $this->routeResponse();
                }

                if (null === $response) {
                    return null;
                }

                if (isset($ewmaHtml)) {
                    $ewmaHtml->setContent($response);

                    return $ewmaHtml->view();
                } else {
                    return $response;
                }
            }
        }
    }

    public function processHandlersOutput($handlersOutputId)
    {
        return $this->c('\ewma\handlers~')->renderOutput($handlersOutputId, $this->data);
    }
}
