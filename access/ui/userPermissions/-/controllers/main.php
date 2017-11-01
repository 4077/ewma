<?php namespace ewma\access\ui\userPermissions\controllers;

class Main extends \Controller
{
    private $s;

    private $user;

    public function __create()
    {
        $this->s = &$this->s(false, [
            'selected_module_path' => ''
        ]);

        $this->user = $this->unpackModel('user') or $this->lock();
    }


    public function reload()
    {
        $this->jquery()->replace($this->view());
    }

    public function view()
    {
        $v = $this->v();

        $user = $this->user;

        $v->assign([
                       'GROUPS'      => $this->c('^ui/userGroups~:view', [
                           'user' => $user
                       ]),
                       'PERMISSIONS' => $this->c('>permissions:view', [
                           'user' => pack_model($this->user),
                       ])
                   ]);

        $this->css();

        return $v;
    }
}
