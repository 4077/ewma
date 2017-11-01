<?php namespace ewma\handlers\ui\cats\controllers;

use ewma\handlers\Cats;

class Input extends \Controller
{
    public $allow = self::XHR;

    private function performCallback($event, $catId)
    {
        if ($context = $this->data('context')) {
            if ($callback = $this->d('contexts:callbacks/' . $event . '|' . $context)) {
                $this->_call($callback)->data('cat_id', $catId)->perform();
            }
        }
    }

    public function create()
    {
        if ($cat = Cats::create()) {
            $this->performCallback('create', $cat->id);
        }
    }

    public function select()
    {
        if ($this->data('cat_id')) {
            $this->performCallback('select', $this->data['cat_id']);
        }
    }
}
