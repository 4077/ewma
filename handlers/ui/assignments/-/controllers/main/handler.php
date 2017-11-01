<?php namespace ewma\handlers\ui\assignments\controllers\main;

use std\router\models\Assignment as AssignmentModel;

class Handler extends \Controller
{
    private $assignment;

    public function __create()
    {
        if ($this->dataHas('assignment')) {
            $this->assignment = $this->data['assignment'];
        }

        if ($this->dataHas('assignment_id')) {
            $this->assignment = AssignmentModel::find($this->data['assignment_id']);
        }

        if ($this->assignment) {
            $this->setContext();
        } else {
            $this->lock();
        }
    }

    private $context;
    private $contextData;

    private function setContext()
    {
        $this->context = $this->data('context');
        $this->contextData = &$this->d('contexts:|' . $this->context);

        if ($this->dataHas('context_data')) {
            $this->contextData = $this->data['context_data'];
        }
    }

    public function reload()
    {
        $usedAssignment = $this->assignment;
        // закосячило при saveAsGlobal
        //$this->assignment->source && $this->assignment->source_used ? $this->assignment->source : $this->assignment;

        $this->jquery("|" . $usedAssignment->id)->replace($this->view());
    }

    /**
     * Отображение обработчика и его контейнеров (переменных и входов)
     *
     * Если обработчик имеет ссылку (source_id) на другой обработчик и ссылка задействована (source_used),
     * то отображается не сам обработчик, а тот, на который он ссылается, и в режиме только для чтения
     * Но при этом все действия производятся над самим обработчиком
     *
     * @return \ewma\Views\View
     */
    public function view()
    {
        $editable = !empty($this->contextData['editable']);

        $handler = $this->assignment;

        $sourceHandler = $handler->source;
        $isFork = null !== $sourceHandler; // имеет источник

        if ($isFork && $handler->source_used) { // источник задействован
            $usedHandler = $sourceHandler;
            $sourceUsed = true;
        } else {
            $usedHandler = $handler;
            $sourceUsed = false;
        }

        $v = $this->v('|' . $handler->id);

        //
        // handler
        //

        $usedHandlerData = _j($usedHandler->data);
        $requestsData = [
            'context'       => $this->context,
            'assignment_id' => $handler->id
        ];

        $v->assign([
                       'HANDLER_ID'     => $this->assignment->id,
                       'ID_INFO'        => $handler->id . ($isFork ? ' < ' . $sourceHandler->id : ''),
                       'DISABLED_CLASS' => $handler->enabled ? '' : 'disabled',
                       'NAME_TXT'       => $this->c('\std\ui txt:view', [
                           'editable'          => !$sourceUsed && $editable,
                           'path'              => 'input:handlerNameUpdate',
                           'data'              => $requestsData,
                           'class'             => 'txt',
                           'title'             => 'Название',
                           'placeholder'       => '...',
                           'fitInputToClosest' => '.bar',
                           'content'           => $usedHandlerData['name']
                       ]),
                       'PATH_TXT'       => $this->c('\std\ui txt:view', [
                           'editable'          => !$sourceUsed && $editable,
                           'path'              => 'input:handlerPathUpdate',
                           'data'              => $requestsData,
                           'class'             => 'txt',
                           'title'             => 'Путь к методу',
                           'emptyContent'      => '...',
                           'fitInputToClosest' => '.bar',
                           'content'           => $usedHandlerData['path']
                       ]),
                       'CP'             => $this->c('~handler/cp:view', [
                           'assignment' => $handler
                       ], 'context')
                   ]);

        if ($handler['required']) {
            $v->assign('required');
        }

        if ($isFork) {
            $v->assign('has_reference', [
                'REFERENCE' => $handler->source_used ? 'G' : 'L'
            ]);
        }

//        if ($editable) {
//            $handlerSelector = $this->_selector("|" . $handler->id) . " > .bar";
//            $this->c('\std\ui button:bind', [
//                'selector' => $handlerSelector,
//                'path'     => 'input:showHandlerCp',
//                'data'     => $requestsData
//            ]);
//        }

        //
        // containers
        //

        $containers = $usedHandler->nested()->with('source')->orderBy('position')->get();
        foreach ($containers as $container) {
            $sourceContainer = $container->source;
            $isFork = null !== $sourceContainer; // имеет источник

            if ($isFork && $container->source_used) { // источник задействован
                $usedContainer = $sourceContainer;
                $sourceUsed = true;
            } else {
                $usedContainer = $container;
                $sourceUsed = false;
            }

            $usedContainerData = _j($usedContainer->data);
            $requestsData = [
                'context'       => $this->context,
                'assignment_id' => $container->id
            ];

            $v->assign('container', [
                'ID'             => $container->id,
                'ID_INFO'        => $container->id . ($isFork ? ' < ' . $sourceContainer->id : ''),
                'DISABLED_CLASS' => $container->enabled ? '' : 'disabled',
                'TYPE_CLASS'     => strtolower($container->type),
                'NAME_TXT'       => $this->c('\std\ui txt:view', [
                    'editable'          => !$sourceUsed && $editable,
                    'path'              => 'input:containerNameUpdate',
                    'data'              => $requestsData,
                    'class'             => 'txt ' . (!$sourceUsed ? '' : 'disabled'),
                    'title'             => 'Название',
                    'placeholder'       => '...',
                    'fitInputToClosest' => '.bar',
                    'content'           => $usedContainerData['name']
                ]),
                'PATH_TXT'       => $this->c('\std\ui txt:view', [
                    'editable'          => !$sourceUsed && $editable,
                    'path'              => 'input:containerPathUpdate',
                    'data'              => $requestsData,
                    'class'             => 'txt ' . (!$sourceUsed ? '' : 'disabled'),
                    'title'             => 'Путь к узлу данных, с которыми будет создан контроллер вызываемого метода',
                    'placeholder'       => '...',
                    'fitInputToClosest' => '.bar',
                    'content'           => $usedContainerData['path']
                ]),
                'CP'             => $this->c('~container/cp:view', [
                    'assignment' => $container
                ], 'context'),
                'ASSIGNMENTS'    => $this->c('@assignments:view', [
                    'context'    => $this->context,
                    'assignment' => $usedContainer
                ])
            ]);

            if ($container->required) {
                $v->assign('container/required');
            }

            if ($isFork) {
                $v->assign('container/has_reference', [
                    'REFERENCE' => $sourceUsed ? 'G' : 'L'
                ]);
            }

            if ($usedContainerData['combine_mode']) {
                $v->assign('container/combine_mode', [
                    'VALUE' => $usedContainerData['combine_mode']
                ]);
            }

//            if ($editable) {
//                $containerSelector = $this->_selector("|" . $handler->id) . " .container[handler_" . $handler->id . "_container_id='" . $container->id . "'] > .bar";
//                $this->c('\std\ui button:bind', [
//                    'selector' => $containerSelector,
//                    'path'     => 'input:showContainerCp',
//                    'data'     => $requestsData
//                ]);
//            }
        }

        //
        // handler
        //

        if ($editable && count($containers) > 1) {
            $this->c('/std/ui sortable:bind', [
                'selector'       => $this->_selector("|" . $this->assignment->id) . " > .containers",
                'path'           => 'input:reorder',
                'items_id_attr'  => 'handler_' . $handler->id . '_container_id',
                'data'           => [
                    'context'    => $this->context,
                    'handler_id' => $handler->id
                ],
                'plugin_options' => [
                    'axis'     => 'y',
                    'distance' => 15
                ]
            ]);
        }

        //
        // ...
        //

        $this->css()->import('common, \jquery\ui icons');
        $this->css('>cp')->import('common');
        $this->css('@container/cp')->import('common');

        return $v;
    }
}
