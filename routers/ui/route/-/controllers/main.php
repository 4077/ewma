<?php namespace ewma\routers\ui\route\controllers;

use ewma\routers\models\Route as RouteModel;
use ewma\routers\Routes;

class Main extends \Controller
{
    private $context;
    private $instance;
    private $route;
    private $contextData;

    public function __create()
    {
        if ($this->data('context') && $route = RouteModel::find($this->data('route_id'))) {
            $this->context = $this->data['context'];
            $this->instance = $route->id;
            $this->route = $route;
            $this->contextData = &$this->d('contexts:|' . $this->context);

            if ($callbacks = $this->data('callbacks')) {
                foreach ($callbacks as $event => $call) {
                    $this->contextData['callbacks'][$event] = $this->_caller()->_abs($call);
                }
            }
        } else {
            $this->lock();
        }
    }

    public function reload()
    {
        $this->jquery(":|" . $this->instance)->replace($this->view());
    }

    public function view()
    {
        $v = $this->v('|' . $this->instance);

        $v->assign([
                       'COMPILE_BUTTON'                     => $this->c('\std\ui button:view', [
                           'path'    => 'input:compile',
                           'data'    => [
                               'context'  => $this->context,
                               'route_id' => $this->route->id
                           ],
                           'class'   => 'compile_button',
                           'content' => 'Скомпилировать'
                       ]),
                       'NAME_TXT'                           => $this->c('\std\ui txt:view', [
                           'path'              => 'input:nameUpdate',
                           'data'              => [
                               'context'  => $this->context,
                               'route_id' => $this->route->id
                           ],
                           'class'             => 'name_txt',
                           'fitInputToClosest' => '.value',
                           'content'           => $this->route->name
                       ]),
                       'BASE_ROUTE_PATTERN'                 => $this->getBaseRoutePattern(),
                       'PATTERN_TXT'                        => $this->c('\std\ui txt:view', [
                           'path'              => 'input:patternUpdate',
                           'data'              => [
                               'context'  => $this->context,
                               'route_id' => $this->route->id
                           ],
                           'class'             => 'pattern_txt',
                           'fitInputToClosest' => '.value',
                           'content'           => $this->route->pattern
                       ]),
                       'TARGET_TYPE_METHOD_BUTTON'          => $this->c('\std\ui button:view', [
                           'path'    => 'input:setTargetType:method',
                           'data'    => [
                               'context'  => $this->context,
                               'route_id' => $this->route->id
                           ],
                           'class'   => 'target_type_button method ' . ($this->route->target_type == 'METHOD' ? 'selected' : ''),
                           'content' => 'вызов метода'
                       ]),
                       'TARGET_TYPE_HANDLERS_OUTPUT_BUTTON' => $this->c('\std\ui button:view', [
                           'path'    => 'input:setTargetType:handlers_output',
                           'data'    => [
                               'context'  => $this->context,
                               'route_id' => $this->route->id
                           ],
                           'class'   => 'target_type_button handlers_output ' . ($this->route->target_type == 'HANDLERS_OUTPUT' ? 'selected' : ''),
                           'content' => 'обработчик'
                       ]),
                       'EWMA_HTML_WRAPPER_BUTTON'           => $this->c('\std\ui button:view', [
                           'path'    => 'input:setResponseWrapper',
                           'data'    => [
                               'context'  => $this->context,
                               'route_id' => $this->route->id,
                               'wrapper'  => 'ewma_html'
                           ],
                           'class'   => 'wrapper_button ewma_html ' . ($this->route->response_wrapper == 'EWMA_HTML' ? 'pressed' : ''),
                           'content' => 'ewma html'
                       ]),
                       'NO_WRAPPER_BUTTON'                  => $this->c('\std\ui button:view', [
                           'path'    => 'input:setResponseWrapper',
                           'data'    => [
                               'context'  => $this->context,
                               'route_id' => $this->route->id,
                               'wrapper'  => 'none'
                           ],
                           'class'   => 'wrapper_button none ' . ($this->route->response_wrapper == 'NONE' ? 'pressed' : ''),
                           'content' => 'none'
                       ]),
                   ]);

        if ($this->route->target_type == 'METHOD') {
            $v->assign('method', [
                'PATH_TXT'   => $this->c('\std\ui txt:view', [
                    'path'              => 'input:targetMethodPathUpdate',
                    'data'              => [
                        'context'  => $this->context,
                        'route_id' => $this->route->id
                    ],
                    'class'             => 'target_method_path_txt',
                    'fitInputToClosest' => '.value',
                    'content'           => $this->route->target_method_path
                ]),
                'DATA_JEDIT' => $this->c('\std\ui\data~:view|' . $this->_nodeId() . '/' . $this->route->id, [
                    'read_call'  => $this->_abs(':readTargetMethodData', [
                        'context'  => $this->context,
                        'route_id' => $this->route->id
                    ]),
                    'write_call' => $this->_abs(':writeTargetMethodData', [
                        'context'  => $this->context,
                        'route_id' => $this->route->id
                    ])
                ])
            ]);
        }

        if ($this->route->target_type == 'HANDLERS_OUTPUT') { // todo OUTPUT_ASSIGNMENT
            $v->assign('handlers_output', [
                'ASSIGNMENTS' => $this->getHandlersOutputView()
            ]);
        }

        $this->css()->import('< common, \css\std~');

        return $v;
    }

    private function getHandlersOutputView()
    {
        if ($this->route->target_type == 'HANDLERS_OUTPUT') {
            if ($output = Routes::getHandlersOutput($this->route->id)) {
                return $this->c('\ewma\handlers\ui\assignments~:outputView', [
                    'output_id'    => $output->id,
                    'context'      => 'route/' . $this->route->id,
                    'context_data' => [
                        'editable'  => true,
                        'callbacks' => [
                            'set_obsolete' => $this->_abs([
                                                              'callbacks:setObsolete',
                                                              [
                                                                  'route_id' => $this->route->id
                                                              ]
                                                          ])
                        ]
                    ]
                ]);
            }
        }
    }

    private function getBaseRoutePattern()
    {
        $route = $this->route;

        $segments = [];
        while ($route = $route->parent) {
            if ($route->pattern) {
                $segments[] = $route->pattern;
            }
        }

        $baseRoute = '';
        if ($segments) {
            $segments = array_reverse($segments);
            $baseRoute = implode('/', $segments) . '/';
        }

        return $baseRoute;
    }

    public function readTargetMethodData()
    {
        return Routes::getTargetMethodData($this->data('route_id'));
    }

    public function writeTargetMethodData()
    {
        if ($route = Routes::setTargetMethodData($this->data('route_id'), $this->data('data'))) {
            $this->c('callbacks:setObsolete', false, 'route_id');
        }
    }
}
