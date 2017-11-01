<?php namespace ewma\access\ui\password\controllers;

class Main extends \Controller
{
    private $user;

    public function __create()
    {
        $this->user = $this->unpackModel('user');
    }

    public function reload()
    {
        $this->jquery()->replace($this->view());
    }

    public function view()
    {
        $v = $this->v();

        $s = &$this->s(false, [
            'password' => '',
            'user_id'  => false
        ]);

        $user = $this->user;

        if ($s['user_id'] != $user->id) {
            $s['password'] = '';
        }

        $s['user_id'] = $user->id;

        $this->c('\std\ui liveinput:bind', [
            'selector' => $this->_selector('|') . " .input input",
            'path'     => '>xhr:update',
            'data'     => [
                'user' => xpack_model($user)
            ]
        ]);

        $v->assign([
                       'PASSWORD'        => $s['password'],
                       'GENERATE_BUTTON' => $this->c('\std\ui button:view', [
                           'path'    => '>xhr:generate',
                           'data'    => [
                               'user' => xpack_model($user)
                           ],
                           'class'   => 'generate_button',
                           'content' => 'сгенерировать'
                       ]),
                       'SET_BUTTON'      => $this->c('\std\ui button:view', [
                           'path'    => '>xhr:set',
                           'data'    => [
                               'user' => xpack_model($user)
                           ],
                           'class'   => 'set_button',
                           'content' => 'Установить'
                       ])
                   ]);

        $this->css(':\css\std~');

        return $v;
    }
}
