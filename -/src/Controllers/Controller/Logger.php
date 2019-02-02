<?php namespace ewma\Controllers\Controller;

class Logger
{
    private $controller;

    private $d;

    private $enabled;

    private $targets;

    private $path;

    private $nodeId;

    public function __construct(\ewma\Controllers\Controller $controller)
    {
        $this->controller = $controller;

        $this->nodeId = $controller->_nodeId();

        $this->setPath($this->nodeId);
    }

    public function setPath($path)
    {
        $this->path = $path;

        $this->d = &$this->controller->d('\ewma~logs:' . $path, [
            'enabled' => true,
            'targets' => [
                'main' => true,
                'path' => true
            ]
        ]);

        $this->enabled = $this->d['enabled'];
        $this->targets = $this->d['targets'];
    }

    private $mainFile;

    private function getMainFile()
    {
        if (null === $this->mainFile) {
            $this->mainFile = fopen(abs_path('logs/main.log'), 'a');
        }

        return $this->mainFile;
    }

    private $pathFile;

    private function getPathFile()
    {
        if (null === $this->pathFile) {
            $filePath = abs_path('logs/' . ($this->path ?: $this->nodeId) . '.log');

            $this->pathFile = fopen($filePath, 'a');
        }

        return $this->pathFile;
    }

    public function write($content)
    {
        if ($this->enabled) {
            $targets = $this->targets;

            if ($targets['main']) {
                fwrite($this->getMainFile(), implode(' ', [dt(), '[' . ($this->path ?: $this->nodeId) . ']', $content]) . PHP_EOL);
            }

            if ($targets['path']) {
                fwrite($this->getPathFile(), implode(' ', [dt(), $content]) . PHP_EOL);
            }
        }
    }
}
