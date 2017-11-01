<?php namespace ewma\access\ui\userControls\login\controllers;

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

        $v->assign([
                       'CONTENT' => $this->c('\std\ui txt:view', [
                           'path'                       => $this->_p('>xhr:update'),
                           'data'                       => [
                               'user' => xpack_model($user)
                           ],
                           'fitInputToClosest'          => '.cell',
                           'editTriggerClosestSelector' => '.cell',
                           'class'                      => 'txt ' . ($this->app->access->getUser($user)->isSuperuser() ? 'su' : ''),
                           'content'                    => $user->login
                       ])
                   ]);

        $this->css();

        $this->e('ewma/access/users/update/login')->rebind(':reload');

        return $v;
    }
}
