<?php namespace ewma\Js;

use ewma\Controllers\Controller;
use ewma\Js\Compiler\Compiler;
use ewma\Js\JsFileUpdater\JsFileUpdater;

class Node
{
    private $app;

    public $controller;

    public $relativePath;

    public $id;

    public function __construct(Controller $controller, $relativePath)
    {
        $this->app = $controller->app;

        $this->controller = $controller;
        $this->relativePath = $relativePath;

        $this->id = $controller->_nodeId($relativePath);
    }

    public function compile($targetDir, $targetFilePath, $compilerSettings)
    {
        $compiler = new Compiler($targetDir, $targetFilePath, $compilerSettings);

        $jsFilePath = $this->controller->_nodeFilePath($this->relativePath, 'js');
        $jsFileAbsPath = abs_path($jsFilePath . '.js');

        if (file_exists($jsFileAbsPath)) {
            $jsFileMTime = filemtime($jsFileAbsPath);
            $jsFileUpdated = !isset($this->app->js->cache['nodes_mtimes'][$this->id]) || $this->app->js->cache['nodes_mtimes'][$this->id] != $jsFileMTime;

            if ($jsFileUpdated) {
                $jsFileUpdater = new JsFileUpdater($jsFilePath);
                $jsFileUpdater->setNodeId($this->controller->_nodeId($this->relativePath));
                $jsFileUpdater->setNodeNs($this->controller->_nodeNs($this->relativePath));
                $jsFileUpdater->setInstance($this->controller->_instance());
                $jsFileUpdater->update();

                $compiler->setSource($jsFilePath, 'js');

                $compiledFilePath = $compiler->compile();

                $this->app->js->cacheUpdateNodeMTime($this->id, $jsFileMTime);
                $this->app->js->cacheUpdateNodeMd5($this->id, md5_file($compiledFilePath));

                return $compiledFilePath;
            }
        } else {
            $message = 'Not found js source with path ' . $jsFilePath;

            if ($this->relativePath) {
                $message .= ' (' . $this->relativePath . '@' . $this->controller->__meta__->absPath . ')';
            }

            $this->app->rootController->console($message);
        }
    }

    public function getFilePath()
    {
        return $this->controller->_nodeFilePath($this->relativePath);
    }

    public function getFingerprint()
    {
        return jmd5($this->id);
    }
}
