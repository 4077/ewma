<?php namespace ewma\handlers\ui\assignments\controllers\main\output;

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

        $requestData = [
            'context'       => $this->context,
            'assignment_id' => $this->assignment->id
        ];

        $v->assign([
                       'ID'                    => $this->assignment->id,
                       //                       'CACHE_OUTPUT_TOGGLE_BUTTON' => $this->c('\std\ui button:view', [
                       //                           'path'    => 'input:handlerCacheEnabledToggle',
                       //                           'data'    => $buttonRequestData,
                       //                           'class'   => 'button',
                       //                           'title'   => $this->assignment->cache_enabled ? 'Выключить кеширование' : 'Включить кеширование',
                       //                           'content' => $this->assignment->cache_enabled ? 'C-' : 'C+',
                       //                       ]),
                       'CREATE_HANDLER_BUTTON' => $this->c('\std\ui button:view', [
                           'path'    => 'input:createHandler',
                           'data'    => $requestData,
                           'class'   => 'button',
                           'title'   => 'Создать обработчик',
                           'content' => '+H'
                       ]),
                       'CREATE_VALUE_BUTTON'   => $this->c('\std\ui button:view', [
                           'path'    => 'input:createVaLue',
                           'data'    => $requestData,
                           'class'   => 'button',
                           'title'   => 'Создать значение',
                           'content' => '+V'
                       ]),
                       'EXCHANGE_BUTTON'       => $this->c('\std\ui button:view', [
                           'path'    => 'input:exchange',
                           'data'    => $requestData,
                           'class'   => 'button',
                           'content' => 'Импорт/экспорт'
                       ]),
                       //                       'ADD_GLOBAL_BUTTON'          => $this->c('\std\ui button:view', [
                       //                           'path'    => 'input:addGlobalDialog',
                       //                           'data'    => $buttonRequestData,
                       //                           'class'   => 'button',
                       //                           'title'   => 'Добавить глобальный обрабочик/значение',
                       //                           'content' => '+G'
                       //                       ]),
                       'PASTE_BUTTON'          => $this->c('\std\ui button:view', [
                           'visible' => $copiedAssignmentId && Assignments::canBePasted($copiedAssignmentType, $this->assignment->type),
                           'path'    => 'input:paste',
                           'data'    => $requestData,
                           'class'   => 'button',
                           'content' => 'Вставить'
                       ]),
                       'NAME_TXT'              => $this->c('\std\ui txt:view', [
                           'path'              => 'input:outputNameUpdate',
                           'data'              => $requestData,
                           'class'             => 'txt',
                           'title'             => 'Имя',
                           'placeholder'       => '...',
                           'fitInputToClosest' => $this->_selector(),
                           'content'           => $this->assignment->name
                       ]),
                   ]);

        return $v;
    }
}
