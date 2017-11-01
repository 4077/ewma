<?php namespace ewma\handlers\ui\assignments\controllers\main\container;

use ewma\handlers\Assignments;

class Cp extends \Controller
{
    private $context;
    private $assignment;

    public function __create()
    {
        $this->context = $this->data('context');
        $this->assignment = $this->data['assignment'];
    }

    public function view()
    {
        $v = $this->v();

        $copiedAssignmentId = $this->s('~:copied_assignment/id');
        $copiedAssignmentType = $this->s('~:copied_assignment/type');

        $buttonRequestData = [
            'context'       => $this->context,
            'assignment_id' => $this->assignment->id
        ];

        foreach (Assignments::$containersCombineModes as $combineMode => $combineModeHint) {
            $v->assign('combine_mode', [
                'SET_BUTTON' => $this->c('\std\ui button:view', [
                    'path'    => 'input:setContainerCombineMode:' . $combineMode,
                    'data'    => $buttonRequestData,
                    'class'   => 'button',
                    'title'   => $combineModeHint,
                    'content' => $combineMode
                ])
            ]);
        }

        $v->assign([
                       'TYPE_CLASS'             => strtolower($this->assignment->type),
                       'ENABLED_TOGGLE_BUTTON'  => $this->c('\std\ui button:view', [
                           'path'    => 'input:containerEnabledToggle',
                           'data'    => $buttonRequestData,
                           'class'   => 'button',
                           'title'   => $this->assignment->enabled ? 'Выключить' : 'Включить',
                           'content' => $this->assignment->enabled ? 'E-' : 'E+'
                       ]),
                       'REQUIRED_TOGGLE_BUTTON' => $this->c('\std\ui button:view', [
                           'path'    => 'input:containerRequiredToggle',
                           'data'    => $buttonRequestData,
                           'class'   => 'button',
                           'content' => $this->assignment->required ? 'R-' : 'R+',
                           'title'   => 'Обработчик будет пропущен если содержание окажется пустым'
                       ]),
                       //                       'CACHE_OUTPUT_TOGGLE_BUTTON' => $this->c('\std\ui button:view', [
                       //                           'path'    => 'input:containerCacheEnabledToggle',
                       //                           'data'    => $buttonRequestData,
                       //                           'class'   => 'button',
                       //                           'title'   => $this->assignment->cache_enabled ? 'Выключить кеширование' : 'Включить кеширование',
                       //                           'content' => $this->assignment->cache_enabled ? 'C-' : 'C+',
                       //                       ]),
                       'CREATE_HANDLER_BUTTON'  => $this->c('\std\ui button:view', [
                           'path'    => 'input:createHandler',
                           'data'    => $buttonRequestData,
                           'class'   => 'button',
                           'title'   => 'Создать обработчик',
                           'content' => '+H'
                       ]),
                       'CREATE_VALUE_BUTTON'    => $this->c('\std\ui button:view', [
                           'path'    => 'input:createValue',
                           'data'    => $buttonRequestData,
                           'class'   => 'button',
                           'title'   => 'Создать значение',
                           'content' => '+V'
                       ]),
                       //                       'CREATE_FROM_SOURCE_BUTTON'  => $this->c('\std\ui button:view', [
                       //                           'path'    => 'input:addGlobalDialog',
                       //                           'data'    => $buttonRequestData,
                       //                           'class'   => 'button',
                       //                           'title'   => 'Добавить глобальный обрабочик/значение',
                       //                           'content' => '+G'
                       //                       ]),
                       'EXCHANGE_BUTTON'        => $this->c('\std\ui button:view', [
                           'path'    => 'input:exchange',
                           'data'    => $buttonRequestData,
                           'class'   => 'button',
                           'content' => 'Импорт/экспорт'
                       ]),
                       'COPY_BUTTON'            => $this->c('\std\ui button:view', [
                           'path'    => 'input:copy',
                           'data'    => $buttonRequestData,
                           'class'   => 'button',
                           'content' => 'Копировать'
                       ]),
                       'PASTE_BUTTON'           => $this->c('\std\ui button:view', [
                           'visible' => $copiedAssignmentId && Assignments::canBePasted($copiedAssignmentType, $this->assignment->type),
                           'path'    => 'input:paste',
                           'data'    => $buttonRequestData,
                           'class'   => 'button',
                           'content' => 'Вставить'
                       ]),
                       'DELETE_BUTTON'          => $this->c('\std\ui button:view', [
                           'ctrl'    => [
                               'path' => 'input:delete',
                               'data' => $buttonRequestData
                           ],
                           'class'   => 'button',
                           'content' => 'Удалить'
                       ])
                   ]);

        return $v;
    }
}
