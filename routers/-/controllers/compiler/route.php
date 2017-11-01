<?php namespace ewma\routers\controllers\compiler;

class Route extends \Controller
{
    public function compile($route, $pattern = null)
    {
        if (null === $pattern) {
            $pattern = $this->getPattern($route);
        }

        if ($route->target_type == 'METHOD') {
            return [
                'pattern'          => $pattern,
                'listen'           => $route->listen,
                'type'             => $route->target_type,
                'path'             => $route->target_method_path,
                'data'             => _j($route->target_method_data),
                'response_wrapper' => $route->response_wrapper
            ];
        }

        if ($route->target_type == 'HANDLERS_OUTPUT') {
            $this->c('\ewma\handlers~:compileOutput', [
                'output_id' => $route->target_handlers_output_id
            ]);

            return [
                'id'                 => $route->id,
                'name'               => $route->name,
                'pattern'            => $pattern,
                'listen'             => $route->listen,
                'type'               => $route->target_type,
                'handlers_output_id' => $route->target_handlers_output_id,
                'response_wrapper'   => $route->response_wrapper
            ];
        }
    }

    private function getPattern($route)
    {
        $segments = [];

        do {
            if ($route->pattern) {
                $segments[] = $route->pattern;
            }
        } while ($route = $route->parent);

        array_reverse($segments);

        return implode('/', $segments);
    }
}
