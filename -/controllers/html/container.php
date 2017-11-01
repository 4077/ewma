<?php namespace ewma\controllers\html;

class Container extends \Controller
{
    public function view()
    {
        $v = $this->v('|');

        $v->assign([
                       'CONTENT' => $this->data('content')
                   ]);

        return $v;
    }
}
