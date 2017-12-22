<?php namespace ewma\dev\ui\node\controllers\main;

class Content extends \Controller
{
    public function reload()
    {
        $this->jquery('|')->replace($this->view());
    }

    public function view()
    {
        $v = $this->v('|');

        $this->smap('~|', 'module_path, node_path, type');

        $type = $this->data['type'];

        if (in($type, 'controller, js, css, less, template')) {
            $code = $this->getCode($type);

            $content = $code
                ? $this->editorView($code, $type)
                : $this->generateControlView($type);
        }

        if ($type == 'session' || $type == 'storage') {
            $content = $this->c('>data:view|');
        }

        $v->assign([
                       'CONTENT' => $content ?? ''
                   ]);

        $this->css();

        return $v;
    }

    private function editorView($code, $type)
    {
        return $this->c('\ace2~:view', [
            'path' => '>xhr:update|',
            'data' => [
                'type' => $type
            ],
            'mode' => $this->getEditorMode(),
            'code' => $code
        ]);
    }

    private function generateControlView($type)
    {
        return $this->c('>generateControl:view|', [
            'type'        => $type,
            'module_path' => $this->data['module_path'],
            'node_path'   => $this->data['node_path']
        ]);
    }

    private function getCode($type)
    {
        $code = $this->d('~:cache/' . $type . '|') or
        $code = read($this->getFilePath());

        return $code;
    }

    private function getFilePath()
    {
        $nodesDir = $this->data['module_path'] ? 'modules/' . $this->data['module_path'] : '';

        $filePath = abs_path($nodesDir, '-', $this->getTypeDir(), $this->data['node_path'] . '.' . $this->getExtension());

        return $filePath;
    }

    private $dataByType = [
        // [type => dir, ext, editor_mode]
        'controller' => ['controllers', 'php', 'php'],
        'js'         => ['js', 'js', 'javascript'],
        'css'        => ['css', 'css', 'css'],
        'less'       => ['less', 'less', 'less'],
        'template'   => ['templates', 'tpl', 'smarty']
    ];

    private function getTypeDir()
    {
        return $this->dataByType[$this->data['type']][0];
    }

    private function getExtension()
    {
        return $this->dataByType[$this->data['type']][1];
    }

    private function getEditorMode()
    {
        return $this->dataByType[$this->data['type']][2];
    }
}
