<?php namespace ewma\handlers\ui\cats\controllers;

use ewma\handlers\models\Cat as CatModel;

class NodeControl extends \Controller
{
    private $cat;
    private $instance;

    public function __create()
    {
        if ($this->data('cat') instanceof CatModel) {
            $this->cat = $this->data['cat'];
            $this->instance = $this->data['context'] . '/' . $this->cat->id;
        } else {
            $this->lock();
        }
    }

    public function reload()
    {
        $this->jquery()->replace($this->view());
    }

    public function view()
    {
        $v = $this->v('|' . $this->instance);

        $allowedActions = $this->data('allowed_actions');

        $isRootNode = $this->data['root_node_id'] == $this->cat->id;

        $v->assign([
                       'ROOT_CLASS'    => $isRootNode ? 'root' : '',
                       'NAME'          => $this->cat->name,
                       'CREATE_BUTTON' => $this->c('\std\ui button:view', [
                           'visible' => !empty($allowedActions['create']),
                           'path'    => 'input:create',
                           'data'    => [
                               'context'  => $this->data('context'),
                               'instance' => $this->data('instance'),
                               'cat_id'   => $this->cat->id
                           ],
                           'class'   => 'button create',
                           'title'   => 'Создать',
                           'content' => '<div class="icon"></div>'
                       ]),
                       'DELETE_BUTTON' => $this->c('\std\ui button:view', [
                           'visible' => !$isRootNode && !empty($allowedActions['delete']),
                           'path'    => 'input:delete',
                           'data'    => [
                               'context'  => $this->data('context'),
                               'instance' => $this->data('instance'),
                               'cat_id'   => $this->cat->id
                           ],
                           'class'   => 'button delete',
                           'title'   => 'Удалить',
                           'content' => '<div class="icon"></div>'
                       ])
                   ]);

        $this->css(':\jquery\ui icons');

        $this->c('\std\ui button:bind', [
            'selector' => $this->_selector('|' . $this->instance),
            'path'     => 'input:select',
            'data'     => [
                'context' => $this->data('context'),
                'cat_id'  => $this->cat->id
            ]
        ]);

        return $v;
    }
}
