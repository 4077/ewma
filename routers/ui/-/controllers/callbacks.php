<?php namespace ewma\routers\ui\controllers;

class Callbacks extends \Controller
{
    //
    // router
    //

    public function routerCreate()
    {
        $selectedRouterId = &$this->s('~:selected_router_id');
        $selectedRouterId = $this->data['router_id'];

        $this->reload();
    }

    public function routerDelete()
    {
        $selectedRouterId = &$this->s('~:selected_router_id');
        if ($selectedRouterId == $this->data['router_id']) {
            $selectedRouterId = false;
        }

        $selectedRoutesStorage = &$this->s('~:selected_route_id_by_router_id');
        if (isset($selectedRoutesStorage[$this->data['router_id']])) {
            unset($selectedRoutesStorage[$this->data['router_id']]);
        }

        $this->reload();
    }

    public function routerUpdate()
    {
        $this->reload();
    }

    public function routerToggleEnabled()
    {
        $this->reload();
    }

    public function routerSelect()
    {
        $selectedRouterId = &$this->s('~:selected_router_id');
        $selectedRouterId = $this->data('router_id');

        $this->reload();
    }

    //
    // route
    //

    public function routeCreate()
    {
        $selectedRouteId = &$this->s('~:selected_route_id_by_router_id/' . $this->data('router_id'));
        $selectedRouteId = $this->data('route_id');

        $this->reload();
    }

    public function routeDelete()
    {
        $selectedRouteId = &$this->s('~:selected_route_id');
        if ($selectedRouteId == $this->data('route_id')) {
            $selectedRouteId = false;
        }

        $this->reload();
    }

    public function routeUpdate()
    {
        $this->reloadRoute();
    }

    public function routeNameUpdate()
    {
        $this->reload();
    }

    public function routeToggleListen()
    {
        $this->reloadRouter();
    }

    public function routeToggleEnabled()
    {
        $this->reloadRouter();
    }

    public function routeSelect()
    {
        $selectedRouteId = &$this->s('~:selected_route_id_by_router_id/' . $this->data('router_id'));
        $selectedRouteId = $this->data('route_id');

        $this->reload();
    }

    //
    // reload
    //

    private function reload()
    {
        $this->c('~:reload');
    }

    private function reloadRouter()
    {
        $selectedRouteId = &$this->s('~:selected_route_id_by_router_id/' . $this->data('router_id'));

        $this->c('router~:reload', [
            'selected_route_id' => $selectedRouteId
        ], 'context, router_id');
    }

    private function reloadRoute()
    {
        $this->c('route~:reload', [

        ], 'context, route_id');
    }
}
