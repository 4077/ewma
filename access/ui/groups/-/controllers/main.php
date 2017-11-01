<?php namespace ewma\access\ui\groups\controllers;

class Main extends \Controller
{
    private $s;

    public function __create()
    {
        $this->s = &$this->s(false, [
            'selected_group_id'    => null,
            'selected_module_path' => null
        ]);
    }

    public function reload()
    {
        $this->jquery()->replace($this->view());
    }

    public function view()
    {
        $v = $this->v();

        $v->assign([
                       'GROUPS' => $this->c('>groups:view')
                   ]);

        $this->c('\std\ui\dialogs~:addContainer:ewma/access/groups');

        if ($group = \ewma\models\access\Group::find($this->s['selected_group_id'])) {
            $v->assign('permissions', [
                'CONTENT' => $this->c('>permissions:view')
            ]);
        }

        $this->css();

        $this->app->html->setFavicon(abs_url('-/ewma/favicons/access_groups.png'));

        $this->e('ewma/access/groups/delete')->rebind(':reload');

        return $v;
    }
}
