<?php namespace ewma\handlers\ui\assignments\controllers\main\handler;

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

        $v->assign([
                       'ENABLED_TOGGLE_BUTTON'  => $this->c('\std\ui button:view', [
                           'path'    => 'input:handlerEnabledToggle',
                           'data'    => $buttonRequestData,
                           'class'   => 'button',
                           'title'   => $this->assignment->enabled ? 'Выключить' : 'Включить',
                           'content' => $this->assignment->enabled ? 'E-' : 'E+'
                       ]),
                       'REQUIRED_TOGGLE_BUTTON' => $this->c('\std\ui button:view', [
                           'path'    => 'input:handlerRequiredToggle',
                           'data'    => $buttonRequestData,
                           'class'   => 'button',
                           'content' => $this->assignment->required ? 'R-' : 'R+',
                           'title'   => 'Контейнер будет пропущен если результат окажется пустым'
                       ]),
                       //                       'CACHE_OUTPUT_TOGGLE_BUTTON' => $this->c('\std\ui button:view', [
                       //                           'path'    => 'input:handlerCacheEnabledToggle',
                       //                           'data'    => $buttonRequestData,
                       //                           'class'   => 'button',
                       //                           'title'   => $this->assignment->cache_enabled ? 'Выключить кеширование' : 'Включить кеширование',
                       //                           'content' => $this->assignment->cache_enabled ? 'C-' : 'C+',
                       //                       ]),
                       'CREATE_VAR_BUTTON'      => $this->c('\std\ui button:view', [
                           'path'    => 'input:createVar',
                           'data'    => $buttonRequestData,
                           'class'   => 'button',
                           'title'   => 'Создать переменную',
                           'content' => '+V'
                       ]),
                       'CREATE_INPUT_BUTTON'    => $this->c('\std\ui button:view', [
                           'path'    => 'input:createInput',
                           'data'    => $buttonRequestData,
                           'class'   => 'button',
                           'title'   => 'Создать вход',
                           'content' => '+I'
                       ]),
                       //                       'SAVE_AS_GLOBAL_BUTTON'      => $this->c('\std\ui button:view', [
                       //                           'path'    => 'input:saveAsGlobal',
                       //                           'data'    => $buttonRequestData,
                       //                           'class'   => 'button',
                       //                           'title'   => 'Сохранить как глобальный',
                       //                           'content' => 'G+'
                       //                       ]),
                       //                       'USE_GLOBAL_BUTTON'          => $this->c('\std\ui button:view', [
                       //                           'path'    => 'input:useGlobal',
                       //                           'data'    => $buttonRequestData,
                       //                           'class'   => 'button',
                       //                           'title'   => 'Использовать глобальную версию',
                       //                           'content' => 'G'
                       //                       ]),
                       //                       'USE_LOCAL_BUTTON'           => $this->c('\std\ui button:view', [
                       //                           'path'    => 'input:useLocal',
                       //                           'data'    => $buttonRequestData,
                       //                           'class'   => 'button',
                       //                           'title'   => 'Использовать локальную версию',
                       //                           'content' => 'L'
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
                               'data' => $buttonRequestData,
                           ],
                           'class'   => 'button',
                           'content' => 'Удалить'
                       ])
                   ]);

        return $v;
    }
}
