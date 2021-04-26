<?php namespace ewma\Controllers\Controller;

class Logger
{
    private $app;

    private $nodeId;

    private $d;

    private $targets = [];

    public function __construct(\ewma\Controllers\Controller $controller)
    {
        $this->app = $controller->app;
        $this->nodeId = $controller->_nodeId();

        $this->d = appd('\ewma~logs:' . $this->nodeId . '|nodes', [
            'enabled'            => true,
            'write_to_node_file' => false,
            'targets'            => [
                [
                    'file_path'     => 'main',
                    'enabled'       => true,
                    'write_app_irc' => true,
                    'write_node_id' => true,
                ]
            ]
        ]);

        if ($this->d['enabled']) {
            $this->renderTargets();
        }
    }

    private function renderTargets()
    {
        if ($this->d['write_to_node_file']) {
            $nodesDir = abs_path('logs/nodes');

            if (!file_exists($nodesDir)) {
                mdir($nodesDir);
            }

            $this->targets[] = [fopen(abs_path('logs/nodes/' . $this->nodeId . '.log'), 'a'), true, false];
        }

        foreach ($this->d['targets'] as $target) {
            if ($target['enabled']) {
                $this->targets[] = [fopen(abs_path('logs/' . $target['file_path'] . '.log'), 'a'), $target['write_app_irc'], $target['write_node_id']];
            }
        }
    }

    public function write($content)
    {
        if ($this->d['enabled']) {
            foreach ($this->targets as $target) {
                [$file, $writeAppIRC, $writeNodeId] = $target;

                fwrite($file, dt() . ' ' . ($writeAppIRC ? $this->app->instanceRandomCode . ' ' : '') . ($writeNodeId ? '[' . $this->nodeId . '] ' : '') . $content . PHP_EOL);
            }
        }
    }
}
