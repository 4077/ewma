<?php namespace ewma\callCenter\controllers\main;

class Calls extends \Controller
{
    public function reload()
    {
        $this->jquery()->replace($this->view());
    }

    public function view()
    {
        $v = $this->v();

        if ($cat = \ewma\callCenter\models\Cat::find($this->s('~:selected_cat_id'))) {
            $calls = $cat->calls()->orderBy('position')->get();

            foreach ($calls as $call) {
                $v->assign('call', [
                    'ID'      => $call->id,
                    'CONTENT' => $this->c('call~:view', [
                        'call' => $call
                    ])
                ]);
            }

            $this->c('\std\ui sortable:bind', [
                'selector'       => $this->_selector(),
                'items_id_attr'  => 'call_id',
                'path'           => '>xhr:rearrange',
                'plugin_options' => [
                    'distance' => 20
                ]
            ]);

            $v->assign([
                           'CREATE_BUTTON' => $this->c('\std\ui button:view', [
                               'path'    => '>xhr:create',
                               'data'    => [
                                   'cat' => xpack_model($cat)
                               ],
                               'class'   => 'create_button',
                               'content' => 'Создать'
                           ])
                       ]);

            $this->e('ewma/callCenter/calls/create', ['cat_id' => $cat->id])->rebind(':reload');
            $this->e('ewma/callCenter/calls/delete', ['cat_id' => $cat->id])->rebind(':reload');

            $this->e('ewma/callCenter/calls/update/cat', ['cat_id' => $cat->id])->rebind(':reload');
        }

        $this->css(':\css\std~');

        return $v;
    }
}
