<?php namespace ewma\handlers\ui\controllers;

class Main extends \Controller
{
    private $s;

    public function __create()
    {
        $this->s = &$this->s(false, [
            'selected_cat_id' => false
        ]);
    }

    public function reload()
    {
        $this->jquery()->replace($this->view());
    }

    public function view()
    {
        $v = $this->v();

        $context = 'catItemsEditor';
        $selectedCatId = $this->s['selected_cat_id'];

        $v->assign([
                       'CATS' => $this->c('cats~:view', [
                           'context'         => $context,
                           'selected_cat_id' => $selectedCatId,
                           'context_data'    => [
                               'allowed_actions' => [
                                   'create' => true,
                                   'delete' => true,
                                   'move'   => true,
                                   'sort'   => true,
                               ],
                               'callbacks'       => [
                                   'create' => $this->_abs('callbacks:createCat'),
                                   'select' => $this->_abs([
                                                               'callbacks:selectCat',
                                                               [
                                                                   'context' => $context
                                                               ]
                                                           ])
                               ]
                           ]
                       ])
                   ]);

        if ($selectedCatId) {
            $v->assign('cat', [
                'SETTINGS' => $this->c('catSettings~:view', [
                    'context'      => $context,
                    'cat_id'       => $selectedCatId,
                    'context_data' => [
                        'callbacks' => [
                            'update_name' => $this->_abs('callbacks:catUpdate'),
                        ]
                    ]
                ]),
                'ITEMS'    => $this->c('catItems~:view', [
                    'context'      => $context,
                    'cat_id'       => $selectedCatId,
                    'context_data' => [
                        'assignments_editable' => true,
                        'allowed_actions'      => [
                            'create' => true,
                        ],
                        'callbacks'            => [
                            'create' => $this->_abs('callbacks:createItem'),
                            'select' => $this->_abs('callbacks:selectItem'),
                        ]
                    ]
                ])
            ]);
        }

        $this->c('\std\ui\dialogs~:addContainer:handlers');

        $this->css();

        return $v;
    }
}
