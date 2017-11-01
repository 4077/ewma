<?php namespace ewma\handlers\ui\assignments\globalSelector\controllers;

class Main extends \Controller
{
    private $s;

    public function __create()
    {
        if ($this->data('context')) {
            $this->s = &$this->s(false, [
                'selected_cat_id' => false
            ]);

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
        $this->jquery()->replace($this->view());
    }

    public function view()
    {
        $v = $this->v();

        $selectedCatId = $this->s['selected_cat_id'];

        $v->assign([
                       'CONTAINER_ID' => '',
                       'CATS'         => $this->c('@cats~:view', [
                           'context'         => $this->context . '/globalSelector',
                           'selected_cat_id' => $selectedCatId,
                           'context_data'    => [
                               'allowed_actions' => [
                                   'create' => false,
                                   'delete' => false,
                                   'move'   => false,
                                   'sort'   => false,
                               ],
                               'callbacks'       => [
                                   'select' => $this->_abs([
                                                               'callbacks:selectCat',
                                                               [
                                                                   'context' => $this->context
                                                               ]
                                                           ])
                               ]
                           ]
                       ])
                   ]);

        if ($selectedCatId) {
            $v->assign('cat_items', [
                'CONTENT' => $this->c('@catItems~:view', [
                    'context'      => $this->context . '/globalSelector',
                    'cat_id'       => $selectedCatId,
                    'context_data' => [
                        'assignments_editable' => false,
                        'allowed_actions'      => [
                            'create' => false,
                        ],
                        'callbacks'            => [
                            'select' => $this->contextData['callbacks']['select'],
                        ]
                    ]
                ])
            ]);
        }

        $this->css();

        return $v;
    }
}
