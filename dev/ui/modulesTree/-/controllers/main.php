<?php namespace ewma\dev\ui\modulesTree\controllers;

class Main extends \Controller
{
    private $s;

    public function __create()
    {
        $this->s = &$this->s('|', [
            'expand_nodes'         => [],
            'selected_module_path' => null
        ]);

        if ($this->dataHas('selected_module_path')) {
            $this->s['selected_module_path'] = $this->data['selected_module_path'];
        }

        $this->setCallbacks();
    }

    private function setCallbacks()
    {
        if ($this->dataHas('callbacks')) {
            $callbacks = &$this->d(':callbacks|');

            ra($callbacks, $this->data['callbacks']);
        }
    }

    public function performCallback($name, $data)
    {
        $callbacks = $this->d(':callbacks|');

        if (isset($callbacks[$name])) {
            $this->_call($callbacks[$name])->ra($data)->perform();
        }
    }

    public function reload()
    {
        $this->jquery('|')->replace($this->view());
    }

    public function view()
    {
        $v = $this->v('|');

        $this->css();

        $this->widget(':|', [
            'paths' => [
                'toggleSubnodes' => $this->_p('>xhr:toggleSubnodes|'),
                'selectModule'   => $this->_p('>xhr:selectModule|')
            ]
        ]);

        $v->assign('TREE', $this->treeView());

        return $v;
    }

    private $tree;
    private $level = 0;

    private function treeView($path = [])
    {
        $this->tree = $this->c('^svcs modules:getTree');

        return $this->treeViewRecursion($path);
    }

    private function treeViewRecursion($modulePathArray)
    {
        $v = $this->v('>nodes');

        $modulePath = a2p($modulePathArray);
        $moduleName = $modulePathArray ? end($modulePathArray) : '/';

        $v->assign('nodes', [
            'MODULE_PATH' => $modulePath
        ]);

        $node = ap($this->tree, $modulePath);

        $expand = $this->isExpand($modulePath);

        $nodeKeys = array_keys($node);

        $hasSubnodes = count(diff($nodeKeys, '-', true));

        $class = '';
        if (null !== $this->s['selected_module_path'] && $this->s['selected_module_path'] == $modulePath) {
            $class .= ' selected';
        }

        $class .= ' ' . (isset($node['-']['settings']['type']) ? $node['-']['settings']['type'] : 'master');

        $v->assign('nodes/node', [
            'NAME'                   => $moduleName,
            'CLASS'                  => $class,
            'INDENT_WIDTH'           => ($this->level + 1) * 16 + 5,
            'INDENT_CLICKABLE_CLASS' => $hasSubnodes ? ' clickable' : '',
            'EXPAND_ICON_CLASS'      => $hasSubnodes ? ($expand ? 'rd_arrow' : 'r_arrow') : 'hidden',
            'MARGIN_LEFT'            => ($this->level) * 16 + 5,
            'NAME_MARGIN_LEFT'       => ($this->level + 1) * 16 + 5
        ]);

        if (true) {
            $v->assign('nodes/node/update_cache', [
                'CLASS' => 'update_cache'
            ]);
        }

        if ($expand) {
            $v->assign('nodes/subnodes', [
                'HIDDEN_CLASS' => ''
            ]);

            foreach ($node as $moduleName => $moduleData) {
                if ($moduleName != '-') {
                    $modulePathArray[] = $moduleName;
                    $this->level++;

                    $v->assign('nodes/subnodes/subnode', [
                        'CONTENT' => $this->treeViewRecursion($modulePathArray)
                    ]);

                    $this->level--;
                    array_pop($modulePathArray);
                }
            }
        }

        return $v;
    }

    private function isExpand($modulePath)
    {
        return in_array($modulePath, $this->s['expand_nodes']);
    }
}
