<?php namespace ewma\dev\ui\node\controllers\main;

class Cp extends \Controller
{
    private $types = ['controller', 'js', 'less', 'template', 'session', 'storage'];

    public function reload()
    {
        $this->jquery('|')->replace($this->view());
    }

    public function view()
    {
        $v = $this->v('|');

        $this->smap('~|', 'module_path, node_path, type');

        $nodeTypes = $this->c('\dev\project services/nodes_tree')->get_node_types($this->data['module_path'], $this->data['node_path']);

        foreach ($this->types as $type) {
            $class = 'tab ' . $type;

            if (in_array($type, $nodeTypes)) {
                $class .= ' has_file';
            }

            if ($this->data['type'] == $type) {
                $class .= ' selected';
            }

            $v->assign('tab', [
                'BUTTON' => $this->c('\std\ui button:view', [
                    'path'    => '>xhr:setType|',
                    'data'    => [
                        'type' => $type
                    ],
                    'class'   => $class,
                    'content' => $type . ($this->d('~:cache/' . $type . '|') ? '*' : '')
                ])
            ]);

            $hasChangedTypes = $this->hasChangedTypes();

            $v->assign([
                           'SAVE_BUTTON' => $this->c('\std\ui button:view', [
                               'visible' => $hasChangedTypes,
                               'path'    => '>xhr:save|',
                               'data'    => [
                                   'type' => $type
                               ],
                               'class'   => 'save_button',
                               'content' => 'save'
                           ])
                       ]);
        }

        $this->css(':common');

        $this->e('ewma/dev/nodeEditor/update/' . $this->data('type') . '/' . $this->_instance())->rebind(':reload|');
        $this->e('ewma/dev/nodeEditor/save/' . $this->_instance())->rebind(':reload|');

        return $v;
    }

    public function hasChangedTypes()
    {
        $cache = $this->d('~:cache|');

        foreach ($cache as $type => $code) {
            if (null !== $code) {
                return true;
            }
        }

        return false;
    }
}
