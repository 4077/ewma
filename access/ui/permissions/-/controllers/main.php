<?php namespace ewma\access\ui\permissions\controllers;

class Main extends \Controller
{
    private $s;

    public function __create()
    {
        $this->s = &$this->s('|', [
            'selected_module_path' => ''
        ]);
    }

    public function reload()
    {
        $this->jquery('|')->replace($this->view());
    }

    public function view()
    {
        $v = $this->v('|');

        $v->assign([
//                       'MODULES'       => $this->c('\ewma\dev\ui\modulesTree~:view|' . $this->_nodeId(), [
//                           'selected_module_path' => $this->s['selected_module_path'],
//                           'callbacks'            => [
//                               'select' => $this->_abs('~app:selectModule')
//                           ]
//                       ]),
//                       'MODULE_SELECT' => $this->c('>modules:view', [
//                           'module_path' => $this->s['selected_module_path']
//                       ]),
                       'PERMISSIONS'   => $this->c('>permissions:view')
                   ]);

        $this->c('\std\ui\dialogs~:addContainer:ewma/access/permissions');

        $this->css();

        $this->app->html->setFavicon(abs_url('-/ewma/favicons/access_permissions.png'));

        return $v;
    }
}
