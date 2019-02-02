<?php namespace ewma\Js\JsFileUpdater;

class Body
{
    private $content;

    public function __construct($content)
    {
        $this->content = $content;
    }

    public function hasNodeIdUsage()
    {
        return false !== strpos($this->content, '__nodeId__');
    }

    public function hasNodeNsUsage()
    {
        return false !== strpos($this->content, '__nodeNs__');
    }

    public function hasInstanceUsage()
    {
        return false !== strpos($this->content, '__instance__');
    }

    public function compile()
    {
        return $this->content;
    }
}
