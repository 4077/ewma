<?php namespace ewma\controllers\main\html;

class Container extends \Controller
{
    public function view()
    {
        $v = $this->v('|');

        $v->assign([
                       'CONTENT' => $this->data('content'),
                       'CLASS'   => $this->data('class')
                   ]);

        return $v;
    }
}
