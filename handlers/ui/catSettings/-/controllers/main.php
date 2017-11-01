<?php namespace ewma\handlers\ui\catSettings\controllers;

use ewma\handlers\models\Cat as CatModel;

class Main extends \Controller
{
    private $cat;

    public function __create()
    {
        if ($this->data('context') && $cat = CatModel::find($this->data('cat_id'))) {
            $this->cat = $cat;
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

    public function view()
    {
        $v = $this->v();

        $v->assign([
                       'NAME_TXT' => $this->c('\std\ui txt:view', [
                           'path'              => 'input:nameUpdate',
                           'data'              => [
                               'context'   => $this->context,
                               'router_id' => $this->cat->id
                           ],
                           'class'             => 'name_txt',
                           'fitInputToClosest' => '.value',
                           'content'           => $this->cat->name
                       ]),
                   ]);

        $this->css();

        return $v;
    }
}