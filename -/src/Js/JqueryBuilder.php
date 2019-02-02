<?php namespace ewma\Js;

use ewma\Views\View;

class JqueryBuilder
{
    private $selector;
    private $nodeId;

    public $code;
    public $callable = true;

    public function __construct($selector = false, $nodeId = false)
    {
        $this->selector = $selector;
        $this->nodeId = $nodeId;

        if (is_null($selector)) {
            $this->code = '$';
        } else {
            if (!in_array($selector, ['window', 'document'])) {
                $selector = '"' . str_replace('"', '\"', $selector) . '"';
            }

            $this->code = '$(' . $selector . ')';
        }
    }

    public function __call($method, array $args)
    {
        return $this->addMethodCall($method, $args);
    }

    private function addMethodCall($method, $args)
    {
        ob_start();
        ?>.<?= $method ?>(<?php

        $callArgs = [];
        foreach ($args as $arg) {
            if ($arg instanceof self) {
                $callArgs[] = $arg->code;
                $arg->callable = false;
            } elseif ($arg instanceof View) {
                $callArgs[] = json_encode($arg->render());
            } elseif (is_array($arg)) {
                $callArgs[] = json_encode($arg);
            } elseif (is_numeric($arg)) {
                $callArgs[] = json_encode($arg);
            } elseif (is_string($arg)) {
                $callArgs[] = json_encode($arg); // todo почему в джсон а не просто?
            }
        }

        ?><?= implode(', ', $callArgs) ?>)<?php
        $this->code .= ob_get_clean();

        return $this;
    }

    //
    //
    //

    public function widget()
    {
        return $this->addMethodCall($this->nodeId, func_get_args());
    }

    public function plugin()
    {
        return $this->addMethodCall($this->nodeId, func_get_args());
    }

    //

    public function replace()
    {
        return $this->addMethodCall('replaceWith', func_get_args());
    }

    public function html()
    {
        return $this->addMethodCall(__FUNCTION__, func_get_args());
    }

    public function text()
    {
        return $this->addMethodCall(__FUNCTION__, func_get_args());
    }

    public function append()
    {
        return $this->addMethodCall(__FUNCTION__, func_get_args());
    }

    public function prepend()
    {
        return $this->addMethodCall(__FUNCTION__, func_get_args());
    }

    public function before()
    {
        return $this->addMethodCall(__FUNCTION__, func_get_args());
    }

    public function after()
    {
        return $this->addMethodCall(__FUNCTION__, func_get_args());
    }

    //

    public function data()
    {
        return $this->addMethodCall(__FUNCTION__, func_get_args());
    }

    public function attr()
    {
        return $this->addMethodCall(__FUNCTION__, func_get_args());
    }

    public function val()
    {
        return $this->addMethodCall(__FUNCTION__, func_get_args());
    }

    //

    public function hide()
    {
        return $this->addMethodCall(__FUNCTION__, func_get_args());
    }

    public function show()
    {
        return $this->addMethodCall(__FUNCTION__, func_get_args());
    }

    public function toggle()
    {
        return $this->addMethodCall(__FUNCTION__, func_get_args());
    }

    //

    public function addClass()
    {
        return $this->addMethodCall(__FUNCTION__, func_get_args());
    }

    public function removeClass()
    {
        return $this->addMethodCall(__FUNCTION__, func_get_args());
    }

    public function toggleClass()
    {
        return $this->addMethodCall(__FUNCTION__, func_get_args());
    }
}