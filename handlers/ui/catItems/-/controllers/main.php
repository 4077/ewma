<?php namespace ewma\handlers\ui\catItems\controllers;

use ewma\handlers\models\Cat as CatModel;
use ewma\handlers\Assignments;

class Main extends \Controller
{
    private $instance;
    private $cat;

    public function __create()
    {
        if ($this->dataHas('context') && $cat = CatModel::find($this->data('cat_id'))) {
            $this->instance = $this->data['cat_id'];
            $this->cat = $cat;
            $this->setContext();
        } else {
            $this->lock();
        }
    }

    private $context;

    /**
     * assignments_editable
     *
     */
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
        $this->jquery("|" . $this->instance)->replace($this->view());
    }

    public function view()
    {
        $v = $this->v('|' . $this->instance);

        $allowedActions = $this->contextData['allowed_actions'];

        $items = $this->cat->items()->orderBy('position')->get();
        foreach ($items as $item) {
            $v->assign('item', [
                'ID'      => $item->id,
                'CONTENT' => $this->c('>item:view', [
                    'context'              => $this->context,
                    'assignments_editable' => $this->contextData['assignments_editable'],
                    'item'                 => $item
                ])
            ]);

            $this->c('\std\ui button:bind', [
                'selector' => $this->_selector(". .item[item_id='" . $item->id . "']"),
                'path'     => 'input:select',
                'data'     => [
                    'context' => $this->context,
                    'item_id' => $item->id
                ]
            ]);
        }

        $this->c('\std\ui sortable:bind', [
            'selector'       => $this->_selector("|" . $this->instance) . " > .items",
            'items_attr_id'  => 'item_id',
            'path'           => 'input:reorder',
            'data'           => [
                'context' => $this->context,
                'cat_id'  => $this->cat->id
            ],
            'plugin_options' => [
                'axis'     => 'y',
                'distance' => 15
            ]
        ]);

        // create buttons
        if (!empty($allowedActions['create'])) {
            foreach (Assignments::$assignmentsTypes as $assignmentType => $hint) {
                if (in($assignmentType, 'OUTPUT, HANDLER, VALUE')) {
                    $v->assign('create_button', [
                        'CONTENT' => $this->c('\std\ui button:view', [
                            'path'    => 'input:create',
                            'data'    => [
                                'context' => $this->context,
                                'cat_id'  => $this->cat->id,
                                'type'    => $assignmentType
                            ],
                            'class'   => 'create_button ' . strtolower($assignmentType),
                            'title'   => $hint,
                            'content' => '+' . $assignmentType
                        ])
                    ]);
                }
            }
        }

        $this->css()->import('@assignments common');

        return $v;
    }
}
