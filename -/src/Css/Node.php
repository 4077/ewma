<?php namespace ewma\Css;

use ewma\Controllers\Controller;
use ewma\Css\Compiler\Compiler;
use ewma\Css\LessFileUpdater\LessFileUpdater;

class Node
{
    private $app;

    private $controller;

    private $relativePath;

    public $id;

    public $instance;

    public function __construct(Controller $controller, $relativePath, $id, $instance)
    {
        $this->app = $controller->app;
        $this->controller = $controller;
        $this->relativePath = $relativePath;
        $this->id = $id;
        $this->instance = $instance;
    }

    private $vars = [];

    public function setVars($varsArray)
    {
        $varsFlat = a2f($varsArray);
        ksort($varsFlat);

        foreach ($varsFlat as $path => $value) {
            $this->vars[str_replace('/', '__', $path)] = empty($value) ? '' : $value;
        }

        return $this;
    }

    private $importPaths = [];
    private $importIds = [];

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
            // проверка импортируемых файлов на измененность
            $hasUpdatedImportFiles = false;
            foreach ($this->importIds as $n => $importId) {
                $importFileAbsPath = abs_path($this->importPaths[$n] . '.less');
                $importFileMTime = filemtime($importFileAbsPath);
                if (!isset($this->app->css->cache['nodes_m_times'][$importId]) || $this->app->css->cache['nodes_m_times'][$importId] != $importFileMTime) {
                    $this->app->css->cacheUpdateNodeMTime($importId, $importFileMTime);
                    $hasUpdatedImportFiles = true;
                }
            }

            $mtimeIndex = $this->id . ($this->instance ? '|' . $this->instance : '');

            if ($this->vars) {
                $varsFingerprint = md5(json_encode($this->vars));
                $mtimeIndex .= ':' . $varsFingerprint;
            }

            $lessFileMTime = filemtime($lessFileAbsPath);
            $lessFileUpdated = !isset($this->app->css->cache['nodes_m_times'][$mtimeIndex]) || $this->app->css->cache['nodes_m_times'][$mtimeIndex] != $lessFileMTime;
            if ($hasUpdatedImportFiles || $lessFileUpdated) {
                $lessFileUpdater = new LessFileUpdater($lessFilePath);
                $lessFileUpdater->setNodeId($this->id);
                $lessFileUpdater->setImportList($this->importPaths);
                $lessFileUpdater->update();

                $compiler = new Compiler($targetDir, $targetFilePath, $compilerSettings);
                $compiler->setSource($lessFilePath, 'less');
                $compiler->setLessVars($this->vars);
                $compiler->setInstance($this->instance);
                $compiler->compile();

                $this->app->css->cacheUpdateNodeMTime($mtimeIndex, $lessFileMTime);

                return true;
            }
        } else {
            $cssFilePath = $this->controller->_nodeFilePath($this->relativePath, 'css');
            $cssFileAbsPath = abs_path($cssFilePath . '.css');
            if ($cssFileAbsPath) {
                $cssFileMTime = filemtime($cssFileAbsPath);
                if (!isset($this->app->css->cache['nodes_m_times'][$this->id]) || $this->app->css->cache['nodes_m_times'][$this->id] != $cssFileMTime) {
                    $compiler = new Compiler($targetDir, $targetFilePath, $compilerSettings);
                    $compiler->setSource($cssFilePath, 'css');
                    $compiler->compile();

                    $this->app->css->cacheUpdateNodeMTime($this->id, $cssFileMTime);

                    return true;
                }
            } else {
                throw new \Exception('Not found less or css source with path ' . $this->relativePath . '@' . $this->controller->__meta__->absPath);
            }
        }
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
