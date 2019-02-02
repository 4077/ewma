<?php namespace ewma\Css\LessFileUpdater;

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

    public function compile()
    {
        return $this->content;
    }
}
