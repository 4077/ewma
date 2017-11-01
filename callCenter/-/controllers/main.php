<?php namespace ewma\callCenter\controllers;

class Main extends \Controller
{
    public function __create()
    {
        $this->a() or $this->lock();
    }

    public function reload()
    {
        $this->jquery()->replace($this->view());
    }

    public function view()
    {
        $v = $this->v();

        $this->s(false, [
            'selected_cat_id'          => false,
            'output_call_id_by_cat_id' => []
        ]);

        $v->assign([
                       'CATS'    => $this->c('>cats:view'),
                       'CALLS'   => $this->c('>calls:view'),
                       'OUTPUT'  => $this->c('>output:view')
                   ]);

        $this->c('\std\ui\dialogs~:addContainer:ewma/callCenter');

        $this->css();

        $this->app->html->setFavicon(abs_url('-/ewma/favicons/dev_callCenter.png'));

        return $v;
    }
}
