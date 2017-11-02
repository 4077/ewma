<?php namespace ewma\Js;

use ewma\Controllers\Controller;
use ewma\Js\Compiler\Compiler;
use ewma\Js\JsFileUpdater\JsFileUpdater;

class Node
{
    private $app;

    private $controller;

    private $relativePath;

    private $id;

    public function __construct(Controller $controller, $relativePath, $id)
    {
        $this->app = $controller->app;
        $this->controller = $controller;
        $this->relativePath = $relativePath;
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getFilePath()
    {
        return $this->controller->_nodeFilePath($this->relativePath);
    }

    public function getFingerprint()
    {
        return md5(json_encode($this->id));
    }

    public function compile($targetDir, $targetFilePath, $compilerSettings)
    {
        $compiler = new Compiler($targetDir, $targetFilePath, $compilerSettings);

        $jsFilePath = $this->controller->_nodeFilePath($this->relativePath, 'js');
        $jsFileAbsPath = abs_path($jsFilePath . '.js');

        if (file_exists($jsFileAbsPath)) {
            $jsFileMTime = filemtime($jsFileAbsPath);
            $jsFileUpdated = !isset($this->app->js->cache['nodes_m_times'][$this->id]) || $this->app->js->cache['nodes_m_times'][$this->id] != $jsFileMTime;

            // узел компилируется если был изменен исходник
            if ($jsFileUpdated) {
                $jsFileUpdater = new JsFileUpdater($jsFilePath);
                $jsFileUpdater->setNodeId($this->controller->_nodeId($this->relativePath));
                $jsFileUpdater->setNodeNs($this->controller->_nodeNs($this->relativePath));
                $jsFileUpdater->setInstance($this->controller->_instance());
                $jsFileUpdater->update();

                $compiler->setSource($jsFilePath, 'js');
                $compiler->compile();

                $this->app->js->cacheUpdateNodeMTime($this->id, $jsFileMTime);

                return true;
            }
        } else {
            throw new \Exception('Not found js source with path ' . $this->relativePath . '@' . $this->controller->__meta__->absPath);
        }
    }
}
