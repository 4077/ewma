<?php namespace ewma\routers\controllers\app;

class Exchange extends \Controller
{
    private $exportOutput = [];

    public function export()
    {
        $route = $this->unpackModel('route') or
        $route = \ewma\routers\models\Route::find($this->data('route_id'));

        if ($route) {
            $tree = \ewma\Data\Tree::get(\ewma\routers\models\Route::orderBy('position'));

            $this->exportOutput['route_id'] = $route->id;
            $this->exportOutput['routes'] = $tree->getFlattenData($route->id);

            $this->exportRecursion($tree, $route);

            return $this->exportOutput;
        }
    }

    private function exportRecursion(\ewma\Data\Tree $tree, $route)
    {
        $output = \ewma\routers\Routes::getHandlersOutput($route->id);

        $this->exportOutput['assignments'][$route->id] = $this->c('\ewma\handlers app/exchange:export', [
            'assignment' => $output
        ]);

        $subRoutes = $tree->getSubnodes($route->id);
        foreach ($subRoutes as $subRoute) {
            $this->exportRecursion($tree, $subRoute);
        }
    }

    public function import()
    {
        $targetRoute = $this->unpackModel('route') or
        $targetRoute = \ewma\routers\models\Route::find($this->data('route_id'));

        $importData = $this->data('data');
        $sourceRouteId = $importData['route_id'];

        $this->importRecursion($targetRoute, $importData, $sourceRouteId, $this->data('skip_first_level'));

        $this->c('ui~:reload');
    }

    private function importRecursion($targetRoute, $importData, $routeId, $skipFirstLevel = false)
    {
        $newRouteData = $importData['routes']['nodes_by_id'][$routeId];

        if ($skipFirstLevel) {
            $newRoute = $targetRoute;
        } else {
            $newRoute = $targetRoute->nested()->create($newRouteData);
        }

        $importDataOutputAssignmentId = $importData['assignments'][$routeId]['assignment_id'];
        $newRouteOutputAssignmentData = $importData['assignments'][$routeId]['assignments']['nodes_by_id'][$importDataOutputAssignmentId];
        $newRouteOutputAssignment = \ewma\handlers\models\Assignment::create($newRouteOutputAssignmentData);

        $newRoute->target_handlers_output_id = $newRouteOutputAssignment->id;
        $newRoute->save();

        $this->c('\ewma\handlers app/exchange:import', [
            'assignment'       => $newRouteOutputAssignment,
            'skip_first_level' => true,
            'data'             => $importData['assignments'][$routeId]
        ]);

        if (!empty($importData['routes']['ids_by_parent'][$routeId])) {
            foreach ($importData['routes']['ids_by_parent'][$routeId] as $sourceRouteId) {
                $this->importRecursion($newRoute, $importData, $sourceRouteId);
            }
        }
    }
}
