<?php namespace ewma\Views;

use ewma\App\App;

class View
{
    private $app;

    public function __construct()
    {
        $this->app = App::getInstance();
    }

    private $templatePath;

    public function setTemplateFilePath($templatePath)
    {
        $this->templatePath = $templatePath;
    }

    private $data = [];

    public function setData($data = [])
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    //
    // assign
    //

    private $iteratorsHeads = [];

    public function assign()
    {
        $args = func_get_args();

        if (isset($args[0])) {
            if (is_array($args[0])) {
                foreach ($args[0] as $variable => $value) {
                    $this->assignVar(false, $variable, $value);
                }
            } else {
                if ($args[0] == strtolower($args[0])) { // iterator
                    $vars = isset($args[1]) ? $args[1] : [];
                    $this->assignIterator($args[0], $vars);
                }

                if ($args[0] == strtoupper($args[0]) && isset($args[1])) { // var
                    $this->assignVar(false, $args[0], $args[1]);
                }
            }
        }

        return $this;
    }

    public function append()
    {
        $args = func_get_args();

        if (count($args) == 2) {

            if ($args[0] == strtolower($args[0]) && is_array($args[1])) { // iterator
                $this->assignIterator($args[0], $args[1], true);
            }

            if ($args[0] == strtoupper($args[0])) { // var
                $this->assignVar(false, $args[0], $args[1], true);
            }
        }

        return $this;
    }

    private function getIteratorHead($path, $next = false)
    {
        $pathArray = explode('/', $path);
        $pathLength = count($pathArray);

        $node = &$this->iteratorsHeads;
        $output = [];

        foreach ($pathArray as $n => $segment) {
            if (isset($node[$segment])) {
                $lastPathNode = $pathLength - 1 == $n;

                if ($lastPathNode && $next) {
                    $node[$segment] = ['.' => $node[$segment]['.'] + 1];
                }
            } else {
                $node[$segment] = ['.' => 0];
            }

            $output[] = $segment;
            $output[] = $node[$segment]['.'];

            $node = &$node[$segment];
        }

        return $output;
    }

    private function assignIterator($path, $vars, $append = false)
    {
        $iteratorRealPath = $this->getIteratorHead($path, !$append);

        if ($vars) {
            foreach ($vars as $name => $value) {
                if ($name == strtoupper($name)) {
                    $this->assignVar($iteratorRealPath, $name, $value);
                }
            }
        } else {
            $node = &$this->data;

            foreach ($iteratorRealPath as $segment) {
                $node = &$node[$segment];
            }

            $node = ['.' => []];
        }
    }

    private function assignVar($path, $name, $value, $append = false)
    {
        if ($value instanceof self) {
            $value = $value->render();
        }

        $node = &$this->data;

        if ($path) {
            foreach ($path as $segment) {
                $node = &$node[$segment];
            }
        }

        if (!isset($node['.'][$name])) {
            $node['.'][$name] = $value;
        } else {
            if ($append) {
                $node['.'][$name] .= $value;
            } else {
                $node['.'][$name] = $value;
            }
        }
    }

    //
    // render
    //

    public function render()
    {
        $compiledTemplatePath = $this->getCompiledTemplatePath();

        if ($compiledTemplatePath) {
            ob_start();
            $__data__ = $this->data;
            include $compiledTemplatePath;

            return ob_get_clean();
        }
    }

    private function getCompiledTemplatePath()
    {
        if (!isset($this->app->views->compiledByFilePath[$this->templatePath])) {
            $templatePath = abs_path($this->templatePath . '.tpl');

            if (file_exists($templatePath)) {
                $compiledTemplatePath = abs_path('cache/templates/' . $this->templatePath . '.php'); // todo app->cachePath

                $compile = false;

                if (file_exists($compiledTemplatePath)) {
                    if (filemtime($templatePath) > filemtime($compiledTemplatePath)) {
                        $compile = true;
                    }
                } else {
                    $compile = true;
                }

                if ($compile) {
                    $compiler = new TemplateCompiler;
                    $compiler->setCode(read($templatePath));

                    write($compiledTemplatePath, $compiler->compile());
                    touch($templatePath, filemtime($compiledTemplatePath));
                }

                $this->app->views->compiledByFilePath[$this->templatePath] = $compiledTemplatePath;
            } else {
                $this->app->rootController->console('Not found template ' . $templatePath);
            }
        }

        if (isset($this->app->views->compiledByFilePath[$this->templatePath])) {
            return $this->app->views->compiledByFilePath[$this->templatePath];
        }
    }
}
