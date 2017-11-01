<?php namespace ewma\routers;

use ewma\handlers\Assignments;
use ewma\routers\models\Route as RouteModel;

class Routes
{
    public static function create($parentRouteId)
    {
        if ($route = RouteModel::find($parentRouteId)) {
            $route = $route->nested()->create([
                                                  'enabled'          => true,
                                                  'target_type'      => 'HANDLERS_OUTPUT',
                                                  'response_wrapper' => 'EWMA_HTML'
                                              ]);

            return $route;
        }
    }

    public static function delete($routeId)
    {
        if ($route = RouteModel::find($routeId)) {
            \ewma\Data\Tree::delete($route);
        }
    }

    public static function toggleListen($routeId)
    {
        if ($route = RouteModel::find($routeId)) {
            $route->listen = !$route->listen;
            $route->save();

            return $route->enabled;
        }
    }

    public static function toggleEnabled($routeId)
    {
        if ($route = RouteModel::find($routeId)) {
            $route->enabled = !$route->enabled;
            $route->save();

            return $route->enabled;
        }
    }

    public static function updateName($routeId, $name)
    {
        if ($route = RouteModel::find($routeId)) {
            $route->name = $name;
            $route->save();
        }
    }

    public static function updatePattern($routeId, $pattern)
    {
        if ($route = RouteModel::find($routeId)) {
            $route->pattern = $pattern;
            $route->save();
        }
    }

    public static function updateTargetType($routeId, $targetType)
    {
        if (in(strtoupper($targetType), 'METHOD, HANDLERS_OUTPUT')) {
            if ($route = RouteModel::find($routeId)) {
                $route->target_type = strtoupper($targetType);
                $route->save();
            }
        }
    }

    public static function updateTargetMethodPath($routeId, $targetMethodPath)
    {
        if ($route = RouteModel::find($routeId)) {
            $route->target_method_path = $targetMethodPath;
            $route->save();
        }
    }

    public static function getTargetMethodData($routeId)
    {
        if ($route = RouteModel::find($routeId)) {
            return _j($route->target_method_data);
        }
    }

    public static function setTargetMethodData($routeId, $data)
    {
        if ($route = RouteModel::find($routeId)) {
            $route->target_method_data = j_($data);
            $route->save();

            return $route;
        }
    }

    public static function updateResponseWrapper($routeId, $wrapper)
    {
        if (in(strtoupper($wrapper), 'NONE, EWMA_HTML')) {
            if ($route = RouteModel::find($routeId)) {
                $route->response_wrapper = strtoupper($wrapper);
                $route->save();
            }
        }
    }

    public static function setHandlersContainerId($routeId, $containerAssignmentId)
    {
        if ($route = RouteModel::find($routeId)) {
            $route->target_handlers_container_id = $containerAssignmentId;
            $route->save();
        }
    }

    public static function getHandlersOutput($routeId)
    {
        if ($route = RouteModel::find($routeId)) {
            if ($route->target_handlers_output_id) {
                if (!$handlersOutput = Assignments::getOutput($route->target_handlers_output_id)) {
                    $handlersOutput = Assignments::createOutput();

                    $route->target_handlers_output_id = $handlersOutput->id;
                    $route->save();
                }
            } else {
                $handlersOutput = Assignments::createOutput();

                $route->target_handlers_output_id = $handlersOutput->id;
                $route->save();
            }

            return $handlersOutput;
        }
    }
}
