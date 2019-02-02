<?php namespace ewma\Js\JsFileUpdater;

class JsFileUpdater
{
    private $filePath;
    private $fileContent;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
        $this->fileContent = read(abs_path($this->filePath) . '.js');
    }

    public function setNodeId($nodeId)
    {
        if ($this->getBody()->hasNodeIdUsage()) {
            $this->getHead()->setNodeId($nodeId);
        }
    }

    public function setNodeNs($nodeNs)
    {
        if ($this->getBody()->hasNodeNsUsage()) {
            $this->getHead()->setNodeNs($nodeNs);
        }
    }

    public function setInstance($instance)
    {
        if ($this->getBody()->hasInstanceUsage()) {
            $this->getHead()->setInstance($instance);
        }
    }

    public function update()
    {
        $headCode = $this->getHead()->compile();
        $bodyCode = $this->getBody()->compile();

        $code = ($headCode ? $headCode . PHP_EOL . PHP_EOL : '') . $bodyCode . PHP_EOL;

        if ($code != $this->fileContent) {
            $absFilePath = abs_path($this->filePath) . '.js';
            $mTime = filemtime($absFilePath);
            write($absFilePath, $code);
            touch($absFilePath, $mTime);
        }
    }

    private $headPattern = '/(\/\/ head \{.*\/\/\ })/s';

    private $head;

    /**
     * @return Head
     */
    private function getHead()
    {
        if (null === $this->head) {
            preg_match($this->headPattern, $this->fileContent, $headMatch);

            $headContent = '';
            if (isset($headMatch[1])) {
                $headContent = $headMatch[1];
            }

            $this->head = new Head($this->filePath, $headContent);
        }

        return $this->head;
    }

    private $body;

    /**
     * @return Body
     */
    private function getBody()
    {
        if (null === $this->body) {
            $bodyContent = trim(preg_replace($this->headPattern, '', $this->fileContent));

            $this->body = new Body($bodyContent);
        }

        return $this->body;
    }
}
