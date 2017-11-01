<?php namespace ewma\handlers\ui\catItems\controllers;

use ewma\handlers\Assignments;
use ewma\handlers\models\Cat as CatModel;
use ewma\handlers\models\CatItem as CatItemModel;

class Input extends \Controller
{
    public $allow = self::XHR;

    private function performCallback($event, $data)
    {
        if ($context = $this->data('context')) {
            $callback = $this->d('contexts:callbacks/' . $event . '|' . $context);

            if ($callback) {
                $this->_call($callback)->ra($data)->perform();
            }
        }
    }

    public function create()
    {
        if (in($this->data('type'), array_keys(Assignments::$assignmentsTypes))) {
            if ($this->data['type'] == 'HANDLER') {
                $assignment = Assignments::createHandler();
            }

            if ($this->data['type'] == 'VALUE') {
                $assignment = Assignments::createValue();
            }

            if ($this->data['type'] == 'OUTPUT') {
                $assignment = Assignments::createOutput();
            }

            if (!empty($assignment) && $cat = CatModel::find($this->data('cat_id'))) {
                $cat->items()->create([
                                          'assignment_id' => $assignment->id
                                      ]);

                $this->performCallback('create', ['cat_id' => $cat->id]);
            }
        }
    }

    public function updateName()
    {
        if ($item = CatItemModel::find($this->data('item_id'))) {
            $txt = \std\ui\Txt::value($this);

            $item->name = $txt->value;
            $item->save();

            $txt->response();
        }
    }

    public function reorder()
    {
        if ($this->dataHas('sequence array')) {
            foreach ($this->data['sequence'] as $n => $itemId) {
                if (is_numeric($n) && $assignment = CatItemModel::find($itemId)) {
                    $assignment->update(['position' => ($n + 1) * 10]);
                }
            }
        }
    }

    public function select()
    {
        $this->performCallback('select', [
            'item_id' => $this->data['item_id']
        ]);
    }
}
