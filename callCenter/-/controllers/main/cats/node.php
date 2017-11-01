<?php namespace ewma\callCenter\controllers\main\cats;

class Node extends \Controller
{
    private $cat;

    private $viewInstance;

    public function __create()
    {
        $this->cat = $this->data['cat'];

        $this->viewInstance = $this->cat->id;
    }

    public function reload()
    {
        $this->jquery('|' . $this->viewInstance)->replace($this->view());
    }

    public function view()
    {
        $v = $this->v('|' . $this->viewInstance);

        $isRootCat = $this->data['root_cat_id'] == $this->cat->id;

        $cat = $this->cat;

        $catXPack = xpack_model($cat);

        $v->assign([
                       'ROOT_CLASS'      => $isRootCat ? 'root' : '',
                       'NAME'            => $isRootCat
                           ? ''
                           : $this->c('\std\ui txt:view', [
                               'path'                => '>xhr:rename',
                               'data'                => [
                                   'cat' => $catXPack
                               ],
                               'class'               => 'txt',
                               'fitInputToClosest'   => '.content',
                               'placeholder'         => '...',
                               'editTriggerSelector' => $this->_selector('|' . $this->viewInstance) . " .rename.button",
                               'content'             => $cat->name
                           ]),
                       'RENAME_BUTTON'   => $isRootCat
                           ? ''
                           : $this->c('\std\ui tag:view', [
                               'attrs'   => [
                                   'class' => 'rename button',
                                   'hover' => 'hover',
                                   'title' => 'Переименовать'
                               ],
                               'content' => '<div class="icon"></div>'
                           ]),
                       'EXCHANGE_BUTTON' => $this->c('\std\ui button:view', [
                           'path'    => '>xhr:exchangeDialog|',
                           'data'    => [
                               'cat' => $catXPack
                           ],
                           'class'   => 'button exchange',
                           'title'   => 'Импорт/экспорт',
                           'content' => '<div class="icon"></div>'
                       ]),
                       'CREATE_BUTTON'   => $this->c('\std\ui button:view', [
                           'path'    => '>xhr:create|',
                           'data'    => [
                               'cat' => $catXPack
                           ],
                           'class'   => 'button create',
                           'title'   => 'Создать',
                           'content' => '<div class="icon"></div>'
                       ]),
                       'DELETE_BUTTON'   => $this->c('\std\ui button:view', [
                           'visible' => !$isRootCat,
                           'path'    => '>xhr:delete|',
                           'data'    => [
                               'cat' => $catXPack
                           ],
                           'class'   => 'button delete',
                           'title'   => 'Удалить',
                           'content' => '<div class="icon"></div>'
                       ])
                   ]);

        $this->css(':\jquery\ui icons');

        if (!$isRootCat) {
            $this->c('\std\ui button:bind', [
                'selector' => $this->_selector('|' . $this->viewInstance),
                'path'     => '>xhr:select|',
                'data'     => [
                    'cat' => $catXPack
                ]
            ]);
        }

        return $v;
    }
}
