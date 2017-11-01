<?php namespace ewma\handlers\ui\catItems\controllers\main;

use ewma\handlers\models\CatItem as CatItemModel;

class Item extends \Controller
{
    private $item;

    public function __create()
    {
        if ($this->data('item') instanceof CatItemModel) {
            $this->item = $this->data['item'];
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
        $v = $this->v('|' . $this->item->id);

        $v->assign([
                       'ASSIGNMENTS' => $this->c('@assignments~:assignmentView', [
                           'context'      => $this->data['context'],
                           'context_data' => [
                               'editable' => $this->data['assignments_editable']
                           ],
                           'assignment'   => $this->item->assignment
                       ]),
                   ]);

        $this->css(':\jquery\ui icons');

        $this->c('\std\ui button:bind', [
            'selector' => $this->_selector('|' . $this->item->id),
            'path'     => 'input:select',
            'data'     => [
                'item_id' => $this->item->id,
                'cat_id'  => $this->data('cat_id')
            ]
        ]);

        return $v;
    }
}