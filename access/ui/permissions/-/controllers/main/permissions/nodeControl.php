<?php namespace ewma\access\ui\permissions\controllers\main\permissions;

class NodeControl extends \Controller
{
    private $node;

    public function __create()
    {
        $this->node = $this->unpackModel('node');

        $this->instance_($this->node->id);
    }

    public function view()
    {
        $v = $this->v('|');

        $node = $this->node;
        $nodeXPack = xpack_model($node);

        $isRootNode = $this->data['root_node_id'] == $node->id;

        $v->assign([
                       'ROOT_CLASS'       => $isRootNode ? 'root' : '',
                       'ENABLED_CLASS'    => $node->enabled ? 'enabled' : '',
                       'COPY_PATH_BUTTON' => !$isRootNode
                           ? $this->c('\std\ui tag:view', [
                               'attrs'   => [
                                   'class' => 'copy_path button',
                                   'title' => 'Копировать путь',
                                   'hover' => 'hover'
                               ],
                               'content' => '<div class="icon"></div>'
                           ])
                           : '',
                       'EXCHANGE_BUTTON'  => $this->c('\std\ui button:view', [
                           'path'    => '>xhr:exchangeDialog|',
                           'data'    => [
                               'node' => $nodeXPack
                           ],
                           'class'   => 'button exchange',
                           'title'   => 'Импорт/экспорт',
                           'content' => '<div class="icon"></div>'
                       ]),
                       'CREATE_BUTTON'    => $this->c('\std\ui button:view', [
                           'path'    => '>xhr:create|',
                           'data'    => [
                               'node' => $nodeXPack
                           ],
                           'class'   => 'button create',
                           'title'   => 'Создать',
                           'content' => '<div class="icon"></div>'
                       ]),
                       'DELETE_BUTTON'    => $this->c('\std\ui button:view', [
                           'visible' => !$isRootNode,
                           'path'    => '>xhr:delete|',
                           'data'    => [
                               'node' => $nodeXPack
                           ],
                           'class'   => 'button delete',
                           'title'   => 'Удалить',
                           'content' => '<div class="icon"></div>'
                       ])
                   ]);

        $mode = $this->data('mode');

        if ($mode == 'name' || $mode == 'both') {
            $v->assign('name', [
                'CONTENT' => $this->c('\std\ui txt:view', [
                    'path'              => '>xhr:updateName',
                    'data'              => [
                        'node' => $nodeXPack
                    ],
                    'class'             => 'txt',
                    'fitInputToClosest' => '.content',
                    'placeholder'       => '...',
                    //                    'content'           => $isRootNode
                    //                        ? '[' . $node->module_namespace . '] ' . ($node->name ? $node->name : '...')
                    //                        : ($node->name ? $node->name : '...'),
                    'content'           => $node->name ? $node->name : '...',
                    'contentOnInit'     => $node->name
                ])
            ]);
        }

        if ($mode == 'segment' || $mode == 'both') {
            if (!$isRootNode) {
                $segment = $this->c('\std\ui txt:view', [
                    'path'              => '>xhr:updatePathSegment',
                    'data'              => [
                        'node' => $nodeXPack
                    ],
                    'class'             => 'txt',
                    'fitInputToClosest' => '.content',
                    'placeholder'       => '...',
                    'content'           => $node->path_segment
                ]);

                $v->assign('segment', [
                    'CONTENT' => $segment
                ]);
            }
        }

        $this->css(':\jquery\ui icons');

        return $v;
    }
}
