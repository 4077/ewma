<?php namespace ewma\Route;

class Route
{
    private $baseRoute;

    private $route;

    private $pattern;

    public function __construct($baseRoute, $route, $pattern)
    {
        $this->baseRoute = $baseRoute;
        $this->route = $route;
        $this->pattern = $pattern;
    }

    public function match($callback = false)
    {
        $route = trim_slashes($this->route);
        $pattern = $this->pattern;

        if (!$pattern) {
            if (!$this->route) {
                return true;
            }
        } else {
            $patternData = $this->getPatternData($pattern);

            preg_match('/' . $patternData['regexp'] . '/', '/' . $route, $matches);

            if ($matches) {
                $excess = $patternData['max_segments_count'] > -1 && count(explode('/', $route)) > $patternData['max_segments_count'];

                if (!$excess) {
                    $passData = [];
                    $passRoute = false;
                    $passBaseRouteArray = p2a($this->baseRoute);

                    $routeMatch = true;

                    foreach ($patternData['segments'] as $n => $segment) {
                        if (isset($matches[$n + 1])) {
                            if ($segment['type'] == 'static') {
                                if ($segment['value'] != $matches[$n + 1]) {
                                    $routeMatch = false;

                                    break;
                                } else {
                                    $passBaseRouteArray[] = $segment['value'];
                                }
                            }

                            if ($segment['type'] == 'var') {
                                $passData[$segment['var_name']] = $matches[$n + 1];
                            }

                            if ($segment['type'] == 'route') {
                                $passRoute = $matches[$n + 1];
                            }
                        }
                    }

                    if ($routeMatch) {
                        if ($callback instanceof \Closure) {
                            $resolved = call_user_func($callback, new RouteMatch($passData, $passRoute));
                        } else {
                            $resolved = true;
                        }

                        if ($resolved) {
                            return [
                                'data'       => $passData,
                                'route'      => $passRoute,
                                'base_route' => a2p($passBaseRouteArray)
                            ];
                        }
                    }
                }
            }
        }
    }

    public function getPatternData($pattern)
    {
        $tmp = $pattern;

        $tmp = str_replace('{', '/{', $tmp);
        $tmp = str_replace('*', '/*', $tmp);
        $tmp = str_replace('//', '/', $tmp);

        $segments = explode('/', trim_slashes($tmp));
        $segmentsData = [];

        $offset = 0;

        $routeStarted = false;
        $regexp = '';

        $maxSegmentsCount = 0;

        foreach ($segments as $n => $segment) {
            $offset = strpos($pattern, $segment, $offset);

            $type = substr($segment, 0, 1) == '*' ? 'route' : (substr($segment, 0, 1) == '{' ? 'var' : 'static');
            $required = $type == 'static' || substr($pattern, $offset - 1, 1) == '/';

            $segmentsData[$n]['type'] = $type;
            $segmentsData[$n]['required'] = $required;

            if ($type == 'var') {
                $segmentsData[$n]['var_name'] = substr($segment, 1, -1);
            }

            if ($type == 'static') {
                $segmentsData[$n]['value'] = $segment;
            }

            $segmentsData[$n]['segment_pattern'] = '[^\/]+';
        }

        foreach ($segmentsData as $n => $segmentData) {
            if ($routeStarted) {
                $regexp .= '(?:';
            } else {
                $maxSegmentsCount++;

                if ($segmentData['type'] == 'route') {
                    $regexp .= '(';

                    $routeStarted = true;
                    $maxSegmentsCount = -1;
                } else {
                    $regexp .= '(?:';
                }
            }

            $regexp .= '(?:\/(' . ($routeStarted ? '?:' : '') . $segmentData['segment_pattern'] . ')';
        }

        $quantifier = [
            // type => [unnecessary, necessary]
            'route'  => ['*', '+'],
            'var'    => ['?', ''],
            'static' => ['', '+']
        ];

        for ($i = count($segmentsData) - 1; $i >= 0; $i--) {
            $regexp .= ')' . ($quantifier[$segmentsData[$i]['type']][$segmentsData[$i]['required']]) . ')';
        }

        return [
            'regexp'             => $regexp,
            'segments'           => $segmentsData,
            'max_segments_count' => $maxSegmentsCount
        ];
    }
}
