<?php namespace ewma\access\ui\users\controllers;

class Main extends \Controller
{
    public function reload()
    {
        $this->jquery()->replace($this->view());
    }

    public function view()
    {
        $v = $this->v();

        $v->assign([
                       'GRID'          => $this->c('>grid:view'),
                       'CREATE_BUTTON' => $this->c('\std\ui button:view', [
                           'path'    => '>xhr:create',
                           'class'   => 'create_button green',
                           'content' => 'Создать пользователя'
                       ])
                   ]);

        $this->c('\std\ui\dialogs~:addContainer:ewma/access/users');

        $this->css(':\css\std~');

        $this->app->html->setFavicon(abs_url('-/ewma/favicons/access_users.png'));

        return $v;
    }
}
