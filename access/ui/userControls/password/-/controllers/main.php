<?php namespace ewma\access\ui\userControls\password\controllers;

class Main extends \Controller
{
    private $user;

    public function __create()
    {
        $this->user = $this->unpackModel('user');

        $this->instance_($this->user->id);
    }

    public function reload()
    {
        $this->jquery('|')->replace($this->view());
    }

    public function view()
    {
        $v = $this->v('|');

        $user = $this->user;

        $mode = $this->data('mode');

        if ($mode == 'compact') {
            $v->assign([
                           'CLASS'   => 'compact',
                           'CONTENT' => $this->c('\std\ui button:view', [
                               'path'                        => $this->_p('>xhr:changeDialog'),
                               'data'                        => [
                                   'user' => xpack_model($user)
                               ],
                               'eventTriggerClosestSelector' => '.cell',
                               'class'                       => 'button password',
                               'title'                       => 'Установить новый пароль',
                               'content'                     => '<div class="icon"></div>'
                           ])
                       ]);
        } else {
            $v->assign([
                           'CLASS'   => 'normal',
                           'CONTENT' => $this->c('\std\ui button:view', [
                               'path'                        => $this->_p('>xhr:changeDialog'),
                               'data'                        => [
                                   'user' => xpack_model($user)
                               ],
                               'eventTriggerClosestSelector' => '.cell',
                               'class'                       => 'button password',
                               'content'                     => 'Установить новый'
                           ])
                       ]);
        }

        $this->css(':\jquery\ui icons');

        return $v;
    }
}
