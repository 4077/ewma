<?php namespace ewma\Css;

use ewma\Controllers\Controller;
use ewma\Css\Compiler\Compiler;
use ewma\Css\LessFileUpdater\LessFileUpdater;

class Node
{
    private $app;

    public $controller;

    public $relativePath;

    public $id;

    public $instance;

    public $code;

    public function __construct(Controller $controller, $relativePath, $instance)
    {
        $this->app = $controller->app;

        $this->controller = $controller;
        $this->relativePath = $relativePath;
        $this->instance = $instance;

        $this->id = $controller->_nodeId($relativePath);
        $this->code = $this->id . ($this->instance ? '|' . $this->instance : '');
    }

    public $vars = [];

    public function setVars($varsArray)
    {
        $varsFlat = a2f($varsArray);
        ksort($varsFlat);

        foreach ($varsFlat as $path => $value) {
            $this->vars[str_replace('/', '__', $path)] = $value ?? '';
        }

        return $this;
    }

    public $importPaths = [];

    public $importIds = [];

    public function import($paths, $controller = null)
    {
        if (null === $controller) {
            $controller = $this->controller;
        }

        foreach (l2a($paths) as $path) {
            $importPath = $controller->_nodeFilePath($path, 'less');

            if (!in_array($importPath, $this->importPaths)) {
                $this->importPaths[] = $importPath;
                $this->importIds[] = $controller->_nodeId($path);
            }
        }

        return $this;
    }

    public function compile($targetDir, $targetFilePath, $compilerSettings)
    {
        $lessFilePath = $this->controller->_nodeFilePath($this->relativePath, 'less');
        $lessFileAbsPath = abs_path($lessFilePath . '.less');

        if (file_exists($lessFileAbsPath)) {
            $hasUpdatedImportFiles = false;

            foreach ($this->importIds as $n => $importId) {
                $importFileAbsPath = abs_path($this->importPaths[$n] . '.less');
                $importFileMTime = filemtime($importFileAbsPath);

                if (!isset($this->app->css->cache['nodes_mtimes'][$importId]) || $this->app->css->cache['nodes_mtimes'][$importId] != $importFileMTime) {
                    $this->app->css->cacheUpdateNodeMTime($importId, $importFileMTime);
                    $this->app->css->cacheUpdateNodeMd5($importId, md5_file($importFileAbsPath));

                    $hasUpdatedImportFiles = true;
                }
            }

            $fullCode = $this->getFullCode();

            $lessFileMTime = filemtime($lessFileAbsPath);
            $lessFileUpdated = !isset($this->app->css->cache['nodes_mtimes'][$fullCode]) || $this->app->css->cache['nodes_mtimes'][$fullCode] != $lessFileMTime;

            if ($hasUpdatedImportFiles || $lessFileUpdated) {
                $lessFileUpdater = new LessFileUpdater($lessFilePath);

                $lessFileUpdater->setNodeId($this->id);
                $lessFileUpdater->setImportList($this->importPaths);
                $lessFileUpdater->update();

                $compiler = new Compiler($targetDir, $targetFilePath, $compilerSettings);

                $compiler->setSource($lessFilePath, 'less');
                $compiler->setLessVars($this->vars);
                $compiler->setInstance($this->instance);

                $compiledFilePath = $compiler->compile();

                $this->app->css->cacheUpdateNodeMTime($fullCode, $lessFileMTime);
                $this->app->css->cacheUpdateNodeMd5($fullCode, md5_file($compiledFilePath));

                return $compiledFilePath;
            }
        } else {
            $cssFilePath = $this->controller->_nodeFilePath($this->relativePath, 'css');
            $cssFileAbsPath = abs_path($cssFilePath . '.css');

            if (file_exists($cssFileAbsPath)) {
                $cssFileMTime = filemtime($cssFileAbsPath);
                if (!isset($this->app->css->cache['nodes_mtimes'][$this->id]) || $this->app->css->cache['nodes_mtimes'][$this->id] != $cssFileMTime) {
                    $compiler = new Compiler($targetDir, $targetFilePath, $compilerSettings);

                    $compiler->setSource($cssFilePath, 'css');

                    $compiledFilePath = $compiler->compile();

                    $this->app->css->cacheUpdateNodeMTime($this->id, $cssFileMTime);
                    $this->app->css->cacheUpdateNodeMd5($this->id, md5_file($compiledFilePath));

                    return $compiledFilePath;
                }
            } else {
                $message = 'Not found less or css source with path ' . $cssFilePath;

                if ($this->relativePath) {
                    $message .= ' (' . $this->relativePath . '@' . $this->controller->__meta__->absPath . ')';
                }

                $this->app->rootController->console($message);
            }
        }
    }

    public function getFullCode()
    {
        $output = $this->code;

        if ($this->importIds) {
            $importsFingerprint = jmd5($this->importIds);

            $output .= ':' . $importsFingerprint;
        }

        if ($this->vars) {
            $varsFingerprint = jmd5($this->vars);

            $output .= ':' . $varsFingerprint;
        }

        return $output;
    }

    public function getDevModeFilePath()
    {
        return $this->controller->_nodeFilePath($this->relativePath) . '.' . $this->getFingerprint();
    }

    public function getFingerprint()
    {
        return jmd5([$this->id, $this->vars, $this->importPaths, $this->instance]);
    }
}
