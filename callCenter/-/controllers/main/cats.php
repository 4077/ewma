<?php namespace ewma\callCenter\controllers\main;

class Cats extends \Controller
{
    public function reload()
    {
        $this->jquery()->replace($this->view());
    }

    public function view()
    {
        $v = $this->v();

        $rootNode = $this->getRootNode();

        $v->assign([
                       'CONTENT' => $this->c('\std\ui\tree~:view|' . $this->_nodeId(), [
                           'default'          => [
                               'query_builder' => '>app:getQueryBuilder'
                           ],
                           'node_control'      => [
                               '>node:view',
                               [
                                   'cat'         => '%model',
                                   'root_cat_id' => $rootNode->id
                               ]
                           ],
                           'root_node_id'     => $rootNode->id,
                           'selected_node_id' => $this->s('~:selected_cat_id'),
                           'movable'          => true,
                           'sortable'         => true,
                           'droppable'        => [
                               'call' => [
                                   'accept'         => $this->_selector('@calls:. .call'),
                                   'source_id_attr' => 'call_id',
                                   'path'           => 'call~app:setCat',
                                   'data'           => [
                                       'cat_id'  => '%target_id',
                                       'call_id' => '%source_id'
                                   ]
                               ]
                           ],
                           'permissions'      => $this->_module()->namespace . ':~'
                       ])
                   ]);

        $this->css();

        $this->e('ewma/callCenter/cats/create')->rebind(':reload');
        $this->e('ewma/callCenter/cats/delete')->rebind(':reload');
        $this->e('ewma/callCenter/cats/import')->rebind(':reload');

        return $v;
    }

    private function getRootNode()
    {
        if (!$node = \ewma\callCenter\models\Cat::where('parent_id', 0)->first()) {
            $node = \ewma\callCenter\models\Cat::create(['parent_id' => 0]);
        }

        return $node;
    }
}
