<?php namespace ewma\handlers\ui\catSelector\controllers;

class Main extends \Controller
{
    public function __create()
    {
        if ($this->data('context')) {
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

        $v->assign([
                       'CATS' => $this->c('@cats~:view', [
                           'context'      => $this->context . '/catSelector',
                           'context_data' => [
                               'allowed_actions' => [
                                   'create' => false,
                                   'delete' => false,
                                   'move'   => false,
                                   'sort'   => false,
                               ],
                               'callbacks'       => [
                                   'select' => $this->contextData['callbacks']['select']
                               ]
                           ]
                       ])
                   ]);

        $this->css();

        return $v;
    }
}
