<?php namespace ewma\handlers\controllers;

class Parser extends \Controller
{
    private $content;

    private $tags = [];

    public function parse()
    {
        $this->content = $this->data('content');

        $this->collectTags();
        $this->replaceTags();

        return $this->content;
    }

    private function collectTags()
    {
        preg_match_all('/\[\[(.*)\]\]/U', $this->content, $tags);

        $cutOffset = 0;
        foreach ($tags[1] as $tag) {
            $offset = strpos(substr($this->content, $cutOffset), '[[' . $tag . ']]') + $cutOffset;
            $length = strlen($tag) + 2;

            $cutOffset = $offset + $length;

            $this->tags[$offset] = [
                'value'  => $tag,
                'length' => $length
            ];
        }

        ksort($this->tags);
    }

    private function replaceTags()
    {
        foreach ($this->tags as $offset => $tagData) {
            $value = $tagData['value'];

            if (false !== $colonPos = strpos($value, ':')) {
                $type = substr($value, 0, $colonPos);
                $instruction = substr($value, $colonPos + 1);

                $result = $this->processTag($type, $instruction);

                $this->replaceTag($offset, $tagData['length'], $result);
            }
        }
    }

    private function processTag($type, $instruction)
    {
        if ($type == 'output') {
            return $this->c('~')->renderOutput($instruction, []);
        }
    }

    private $replaceTagsOffsetCorrection = 0;

    private function replaceTag($offset, $length, $replacement)
    {
        $this->content =
            substr($this->content, 0, $offset + $this->replaceTagsOffsetCorrection) .
            $replacement .
            substr($this->content, $offset + $this->replaceTagsOffsetCorrection + $length);

        $this->replaceTagsOffsetCorrection += strlen($replacement) - $length;
    }
}
