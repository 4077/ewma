<?php namespace ewma\handlers\ui\assignments\controllers\main\value;

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

        $buttonRequestData = [
            'context'       => $this->context,
            'assignment_id' => $this->assignment->id
        ];

        foreach (Assignments::$valuesTypes as $valueType => $valueTypeHint) {
            $v->assign('type', [
                'SET_BUTTON' => $this->c('\std\ui button:view', [
                    'path'    => 'input:setValueType:' . $valueType,
                    'data'    => $buttonRequestData,
                    'class'   => 'button',
                    'title'   => $valueTypeHint,
                    'content' => $valueType
                ]),
            ]);
        }

        $v->assign([
                       'ENABLED_TOGGLE_BUTTON' => $this->c('\std\ui button:view', [
                           'path'    => 'input:handlerEnabledToggle',
                           'data'    => $buttonRequestData,
                           'class'   => 'button',
                           'title'   => $this->assignment->enabled ? 'Выключить' : 'Включить',
                           'content' => $this->assignment->enabled ? 'E-' : 'E+'
                       ]),
//                       'SAVE_AS_GLOBAL_BUTTON' => $this->c('\std\ui button:view', [
//                           'path'    => 'input:saveAsGlobal',
//                           'data'    => $buttonRequestData,
//                           'class'   => 'button',
//                           'title'   => 'Сохранить как глобальное',
//                           'content' => 'G+'
//                       ]),
//                       'USE_GLOBAL_BUTTON'     => $this->c('\std\ui button:view', [
//                           'path'    => 'input:useGlobal',
//                           'data'    => $buttonRequestData,
//                           'class'   => 'button',
//                           'title'   => 'Использовать глобальную версию',
//                           'content' => 'G'
//                       ]),
//                       'USE_LOCAL_BUTTON'      => $this->c('\std\ui button:view', [
//                           'path'    => 'input:useLocal',
//                           'data'    => $buttonRequestData,
//                           'class'   => 'button',
//                           'title'   => 'Использовать локальную версию',
//                           'content' => 'L'
//                       ]),
                       'COPY_BUTTON'           => $this->c('\std\ui button:view', [
                           'path'    => 'input:copy',
                           'data'    => $buttonRequestData,
                           'class'   => 'button',
                           'content' => 'Копировать'
                       ]),
                       'DELETE_BUTTON'         => $this->c('\std\ui button:view', [
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
