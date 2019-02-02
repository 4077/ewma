<?php namespace ewma\Js\JsFileUpdater;

class Head
{
    private $filePath;

    public function __construct($filePath, $content)
    {
        $this->filePath = $filePath;

        $this->parse($content);
    }

    private $originalContent;
    private $originalNodeId;
    private $originalNodeNs;
    private $originalInstance;

    private function parse($content)
    {
        $this->originalContent = $content;

        if (preg_match('/var __nodeId__ = "(.*)";/', $content, $nodeIdMatch)) {
            $this->originalNodeId = $nodeIdMatch[1];
        }

        if (preg_match('/var __nodeNs__ = "(.*)";/', $content, $nodeNsMatch)) {
            $this->originalNodeNs = $nodeNsMatch[1];
        }

        if (preg_match('/var __instance__ = "(.*)";/', $content, $nodeNsMatch)) {
            $this->originalInstance = $nodeNsMatch[1];
        }
    }

    private $nodeId;

    public function setNodeId($nodeId)
    {
        $this->nodeId = $nodeId;
    }

    private $nodeNs;

    public function setNodeNs($nodeNs)
    {
        $this->nodeNs = $nodeNs;
    }

    private $instance;

    public function setInstance($instance)
    {
        $this->instance = $instance;
    }

    private function isChanged()
    {
        if ($this->originalNodeId != $this->nodeId) {
            return true;
        }

        if ($this->originalNodeNs != $this->nodeNs) {
            return true;
        }

        if ($this->originalInstance != $this->instance) {
            return true;
        }
    }

    public function compile()
    {
        if ($this->isChanged()) {
            $output = [];

            if ($this->nodeId) {
                $output[] = 'var __nodeId__ = "' . $this->nodeId . '";';
            }

            if ($this->nodeNs) {
                $output[] = 'var __nodeNs__ = "' . $this->nodeNs . '";';
            }

            if ($this->instance) {
                $output[] = 'var __instance__ = "' . $this->instance . '";';
            }

            if ($output) {
                return
                    '// head {' .
                    PHP_EOL .
                    implode(PHP_EOL, $output) .
                    PHP_EOL .
                    '// }';
            } else {
                return '';
            }

        } else {
            return $this->originalContent;
        }
    }
}
