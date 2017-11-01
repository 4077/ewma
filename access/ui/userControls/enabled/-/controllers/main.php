<?php namespace ewma\access\ui\userControls\enabled\controllers;

class Main extends \Controller
{
    private $user;

    private $viewInstance;

    private $eventInstance;

    private $d;

    public function __create()
    {
        $this->user = $this->unpackModel('user');

        $this->viewInstance = path($this->_instance(), $this->user->id);
        $this->eventInstance = $this->_nodeInstance();

        $this->d = &$this->d('|');

        remap($this->d, $this->data, 'mode');
    }

    public function reload()
    {
        $this->jquery('|' . $this->viewInstance)->replace($this->view());
    }

    public function view()
    {
        $v = $this->v('|' . $this->viewInstance);

        $user = $this->user;

        $mode = $this->d['mode'];

        if ($mode == 'compact') {
            $v->assign([
                           'CLASS'   => 'compact',
                           'CONTENT' => $this->c('\std\ui button:view', [
                               'path'                        => $this->_p('>xhr:toggle'),
                               'data'                        => [
                                   'user' => xpack_model($user)
                               ],
                               'eventTriggerClosestSelector' => '.cell',
                               'class'                       => 'button ' . ($user->enabled ? 'checked' : ''),
                               'title'                       => $user->enabled ? 'Выключить' : 'Включить',
                               'content'                     => '<div class="icon"></div>'
                           ])
                       ]);
        } else {
            $v->assign([
                           'CLASS'   => 'normal',
                           'CONTENT' => $this->c('\std\ui button:view', [
                               'path'                        => $this->_p('>xhr:toggle'),
                               'data'                        => [
                                   'user' => xpack_model($user)
                               ],
                               'eventTriggerClosestSelector' => '.cell',
                               'class'                       => 'button ' . ($user->enabled ? 'enabled' : ''),
                               'content'                     => $user->enabled ? 'да' : 'нет'
                           ])
                       ]);
        }

        $this->css(':\jquery\ui icons');

        $this->e('ewma/access/users/update/enabled|' . $this->eventInstance)->rebind(':reload|');

        return $v;
    }
}
