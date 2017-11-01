<?php namespace ewma\handlers\ui\cats\controllers;

use ewma\handlers\models\Cat as CatModel;

class Main extends \Controller
{
    private $instance;

    public function __create()
    {
        if ($this->data('context')) {
            $this->instance = $this->data['context'];

            $this->setContext();
        } else {
            $this->lock();
        }
    }

    private $context;
    private $contextData;

    private function setContext()
    {
        $this->context = $this->data('context');
        $this->contextData = &$this->d('contexts:|' . $this->context);

        if ($this->dataHas('context_data')) {
            $this->contextData = $this->data['context_data'];
        }
    }

    public function reload()
    {
        $this->jquery(":|" . $this->instance)->replace($this->view());
    }

    public function view()
    {
        $v = $this->v('|' . $this->instance);

        $v->assign([
                       'TREE' => $this->treeView()
                   ]);

        $this->css();

        return $v;
    }

    private function treeView()
    {
        $rootCat = CatModel::where('parent_id', 0)->first();

        if (!$rootCat) {
            $rootCat = CatModel::create([
                                            'parent_id' => 0,
                                            'name'      => 'Без категории'
                                        ]);
        }

        $allowedActions = $this->contextData['allowed_actions'];

        $nodeControl = [
            'nodeControl:view',
            [
                'cat'             => '%model',
                'context'         => $this->context,
                'root_node_id'    => $rootCat->id,
                'allowed_actions' => [
                    'create' => !empty($allowedActions['create']),
                    'delete' => !empty($allowedActions['delete']),
                ]
            ]
        ];

        // не понадобится, не стал исправлять
        return $this->c('\std\ui\tree~:view|' . path($this->_nodeId(), $this->context), [
            'aa' => [
                'model'          => CatModel::class,
                'rootNodeId'     => $rootCat->id,
                'valueField'     => 'name',
                'positionField'  => 'position',
                'movable'        => !empty($allowedActions['move']),
                'sortable'       => !empty($allowedActions['sort']),
                'selectedNodeId' => $this->data('selected_cat_id'),
                'expand'         => true,
                'nodeControl'    => $nodeControl
            ],
            'ra' => [
                'rootNodeId'     => $rootCat->id,
                'selectedNodeId' => $this->data('selected_cat_id'),
                'nodeControl'    => $nodeControl,
                'movable'        => !empty($allowedActions['move']),
                'sortable'       => !empty($allowedActions['sort']),
            ]
        ]);
    }
}
