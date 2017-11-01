<?php namespace ewma\cache\controllers;

class Main extends \Controller
{
    public function reset()
    {
        $report = [];

        $all = empty($this->data);

        if ($all || $this->dataHas('autoload')) {
            if ($this->resetAutoload()) {
                $report[] = 'autoload';
            }
        }

        if ($all || $this->dataHas('modules')) {
            if ($this->resetModules()) {
                $report[] = 'modules';
            }
        }

        if ($all || $this->dataHas('cssCompiler')) {
            if ($this->resetCssCompiler()) {
                $report[] = 'cssCompiler';
            }
        }

        if ($all || $this->dataHas('jsCompiler')) {
            if ($this->resetJsCompiler()) {
                $report[] = 'jsCompiler';
            }
        }

        if ($all || $this->dataHas('pathResolver')) {
            if ($this->resetPathResolver()) {
                $report[] = 'pathResolver';
            }
        }

        if ($all || $this->dataHas('templates')) {
            if ($this->resetTemplates()) {
                $report[] = 'templates';
            }
        }

        return 'reseted: ' . ($report ? implode(', ', $report) : 'none');
    }

    private function resetAutoload()
    {
        $filePath = abs_path('cache/autoload.php');

        if (file_exists($filePath)) {
            unlink($filePath);

            return true;
        }
    }

    private function resetModules()
    {
        return $this->app->cache->reset('modules');
    }

    private function resetPathResolver()
    {
        $this->app->paths->resolver->noSave();

        return $this->app->cache->reset('pathResolver');
    }

    private function resetCssCompiler()
    {
        return $this->app->cache->reset('cssCompiler');
    }

    private function resetJsCompiler()
    {
        return $this->app->cache->reset('jsCompiler');
    }

    private function resetTemplates()
    {
        return $this->app->cache->reset('templates');
    }
}
