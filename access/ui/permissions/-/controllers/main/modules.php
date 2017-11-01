<?php namespace ewma\access\ui\permissions\controllers\main;

class Modules extends \Controller
{
    private $module;

    public function __create()
    {
        $this->module = $this->app->modules->getByPath($this->data('module_path'));
    }

    public function reload()
    {
        $this->jquery()->replace($this->view());
    }

    public function view()
    {
        $v = $this->v();

        $items = \ewma\models\access\Permission::select('module_namespace')
            ->groupBy('module_namespace')
            ->orderBy('module_namespace')
            ->get()
            ->pluck('module_namespace')
            ->all();

        $v->assign([
                       'CONTENT' => $this->c('\std\ui select:view', [
                           'path'     => '>xhr:selectModule',
                           'items'    => $items,
                           'selected' => $this->module->namespace
                       ])
                   ]);

        $this->css();

        return $v;
    }
}
