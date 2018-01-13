<?php namespace ewma\dev\ui\node\controllers\main\content;

class Data extends \Controller
{
    public function reload()
    {
        $this->jquery('|')->replace($this->view());
    }

    public function view()
    {
        $v = $this->v('|');

        $this->smap('~|', 'module_path, node_path, type');

        $type = $this->data('type');

        $dataInstance = path($this->data['module_path'], '_', $this->data['node_path']);

        $s = &$this->s('|' . $dataInstance, ['node_instance' => '']);

        foreach ($this->getNodeInstances($type) as $nodeInstance) {
            $requestData = $this->data;
            $requestData['node_instance'] = $nodeInstance;

            $v->assign('instance', [
                'SELECT_BUTTON' => $this->c('\std\ui button:view', [
                    'path'    => '>xhr:selectInstance|',
                    'data'    => [
                        'node_instance' => $nodeInstance
                    ],
                    'content' => ($nodeInstance ? $nodeInstance : '----') . ($s['node_instance'] == $nodeInstance ? '<----' : '')
                ])
            ]);
        }

        $editorInstance = 'dev/sessions/_/' . $dataInstance . '/_/' . $s['node_instance'];
        $nodePath = '/' . $this->data['module_path'] . ' ' . $this->data['node_path'] . ':|' . $s['node_instance'];

        $v->assign('CONTENT', $this->c('\std\ui\data~:view|' . $editorInstance, [
            'read_call'  => $this->_abs(':readNode:' . $type . '|', ['path' => $nodePath]),
            'write_call' => $this->_abs(':writeNode:' . $type . '|', ['path' => $nodePath]),
            'expand'     => true
        ]));

        $this->css();

        return $v;
    }

    private function getNodeInstances($type)
    {
        $module = $this->app->modules->getByPath($this->data['module_path']);
        $moduleNamespace = $module->namespace;

        $nodeInstances = [];

        if ($type == 'session') {
            $nodeInstances = \ewma\models\Session::where('module_namespace', $moduleNamespace)
                ->where('node_path', $this->data['node_path'])
                ->where('key', $this->app->session->getKey())
                ->orderBy('node_instance')
                ->get();
        }

        if ($type == 'storage') {
            $nodeInstances = \ewma\models\Storage::where('module_namespace', $moduleNamespace)
                ->where('node_path', $this->data['node_path'])
                ->orderBy('node_instance')
                ->get();
        }

        $output = [];
        foreach ($nodeInstances as $nodeInstance) {
            $output[] = $nodeInstance['node_instance'];
        }

        return $output;
    }

    public function readNode($type)
    {
        if ($type == 'session') {
            return apps($this->data['path']);
        }

        if ($type == 'storage') {
            return appd($this->data['path']);
        }
    }

    public function writeNode($type)
    {
        if ($type == 'session') {
            apps($this->data['path'], $this->data['data'], RR);
        }

        if ($type == 'storage') {
            appd($this->data['path'], $this->data['data'], RR);
        }

        $this->c('~:performCallback:update|');
    }
}
