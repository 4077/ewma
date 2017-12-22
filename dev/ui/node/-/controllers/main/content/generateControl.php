<?php namespace ewma\dev\ui\node\controllers\main\content;

class GenerateControl extends \Controller
{
    public function view()
    {
        $v = $this->v();

        $type = $this->data['type'];

        $templates = $this->c('\dev\project\module\generators~:get_templates:' . $type);

        foreach ($templates as $template) {
            $v->assign('template', [
                'BUTTON' => $this->c('\std\ui button:view', [
                    'path'    => '>xhr:generate|',
                    'data'    => [
                        'type'        => $type,
                        'template'    => $template,
                        'module_path' => $this->data['module_path'],
                        'node_path'   => $this->data['node_path']
                    ],
                    'class'   => 'create_template_button ' . $type,
                    'content' => $template
                ])
            ]);
        }

        $this->css(':common');

        return $v;
    }
}
