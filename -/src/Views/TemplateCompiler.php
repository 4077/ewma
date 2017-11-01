<?php namespace ewma\Views;

class TemplateCompiler
{
    private $code;

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function compile()
    {
        $this->code = preg_replace('/\{\*.*\*\}/Us', '', $this->code);

        $this->collectTags();
        $this->replaceTags();

        return $this->code;
    }

    //
    // collect tags
    //

    private $tags = [];

    private function collectTags()
    {
        $this->collectIterators();
        $this->collectVars();

        ksort($this->tags);
    }

    private function collectIterators()
    {
        preg_match_all('/<!-- (.*) -->/U', $this->code, $iterators);

        $cutOffset = 0;
        foreach ($iterators[1] as $iterator) {
            $offset = strpos(substr($this->code, $cutOffset), '<!-- ' . $iterator . ' -->') + $cutOffset;
            $length = strlen($iterator) + 9;

            $cutOffset = $offset + $length;

            if ($iterator == '/') {
                $type = 'close';
            } else {
                if (strpos($iterator, 'if ') === 0) {
                    $type = 'cond';
                } else {
                    $type = 'iterator';
                }
            }

            $this->tags[$offset] = [
                'type'   => $type,
                'value'  => $iterator,
                'length' => $length
            ];
        }
    }

    private function collectVars()
    {
        preg_match_all('/{(.*)}/U', $this->code, $vars);

        $cutOffset = 0;
        foreach ($vars[1] as $var) {
            $offset = strpos(substr($this->code, $cutOffset), '{' . $var . '}') + $cutOffset;
            $length = strlen($var) + 2;

            $cutOffset = $offset + $length;

            $this->tags[$offset] = [
                'type'   => 'var',
                'value'  => $var,
                'length' => $length
            ];
        }
    }

    //
    // replace tags
    //

    private function replaceTags()
    {
        foreach ($this->tags as $offset => $tagData) {
            if ($tagData['type'] == 'iterator') {
                $this->replaceIteratorOpenTag($offset, $tagData);
            }

            if ($tagData['type'] == 'cond') {
                $this->replaceCondOpenTag($offset, $tagData);
            }

            if ($tagData['type'] == 'close') {
                $this->replaceCloseTag($offset, $tagData);
            }

            if ($tagData['type'] == 'var') {
                $this->replaceVarTag($offset, $tagData);
            }
        }
    }

    private $openTagsTypesStack = [];

    private function replaceCondOpenTag($offset, $tagData) // todo if VARIABLE, if iterator_1/iterator_N/VARIABLE,
    {
        $expression = explode(' ', $tagData['value']);

        if ($expression[1] == 'not') {
            $condStr = 'empty';
            $iterator = $expression[2];
        } else {
            $condStr = '!empty';
            $iterator = $expression[1];
        }

        $iterator = explode('/', $iterator);

        if (count($iterator) == 1) {
            $replacement = '<?php if (' . $condStr . '($__data__[\'' . $iterator[0] . '\'])) {{ ?>';
        } else {
            if ($this->onCurrentBranch($iterator)) {
                $replacement = '<?php if (' . $condStr . '($' . implode('_', array_slice($iterator, 0, -1)) . '[\'' . end($iterator) . '\'])) {{ ?>';
            }
        }

        if (isset($replacement)) {
            $this->openTagsTypesStack[] = 'cond';

            $this->replaceTag($offset, $tagData['length'], $replacement);
        }
    }

    private function replaceIteratorOpenTag($offset, $tagData)
    {
        $iterator = explode('/', $tagData['value']);

        if (count($iterator) == 1) {
            $iteratorStr = '$__data__[\'' . $iterator[0] . '\']';
            $replacement = '<?php if (isset(' . $iteratorStr . ')) { foreach (' . $iteratorStr . ' as $' . $iterator[0] . ') { ?>';

            $this->addIteratorBranch($iterator[0]);
        } else {
            if ($this->onCurrentBranch($iterator)) {
                $iteratorStr = '$' . implode('_', array_slice($iterator, 0, -1)) . '[\'' . end($iterator) . '\']';
                $replacement = '<?php if (isset(' . $iteratorStr . ')) { foreach ( ' . $iteratorStr . ' as $' . implode('_', $iterator) . ') { ?>';

                $this->addIteratorBranchNode(end($iterator));
            } else {
                throw new \Exception('Wrong open iterator path '.a2p($iterator));
            }
        }

        if (isset($replacement)) {
            $this->openTagsTypesStack[] = 'iterator';

            $this->replaceTag($offset, $tagData['length'], $replacement);
        }
    }

    private function replaceCloseTag($offset, $tagData)
    {
        $replacement = '<?php }} ?>';

        $this->replaceTag($offset, $tagData['length'], $replacement);

        $lastOpenedTagType = array_pop($this->openTagsTypesStack);
        if ($lastOpenedTagType != 'cond') {
            $this->removeIteratorBranchLastNode();
        }
    }

    private function replaceVarTag($offset, $tagData)
    {
        $varPath = explode('/', $tagData['value']);

        if (count($varPath) == 1) {
            if (substr($varPath[0], 0, 1) == '~') {
                $varCode = '$__data__[\'.\'][\'' . substr($varPath[0], 1) . '\']';
            } else {
                $currentIteratorBranch = $this->getCurrentIteratorBranch();

                $varCode = '$' . ($currentIteratorBranch ? implode('_', $currentIteratorBranch) : '__data__') . '[\'.\'][\'' . $varPath[0] . '\']';
            }

            $replacement = '<?= isset(' . $varCode . ') ? ' . $varCode . ' : \'\' ?>';
        } else {
            $iteratorBranch = $this->getIteratorBranch($varPath[0]);
            if ($iteratorBranch) {
                $varIteratorBranch = array_slice($varPath, 0, -1);

                $branchesMatch = true;
                foreach ($varIteratorBranch as $n => $node) {
                    if ($iteratorBranch[$n] != $node) {
                        $branchesMatch = false;
                        break;
                    }
                }

                if ($branchesMatch) {
                    $varCode = '$' . implode('_', $varIteratorBranch) . '[\'.\'][\'' . end($varPath) . '\']';
                    $replacement = '<?= isset(' . $varCode . ') ? ' . $varCode . ' : \'\' ?>';
                } else {
                    throw new \Exception('Wrong open iterator path');
                }
            }
        }

        isset($replacement) && $this->replaceTag($offset, $tagData['length'], $replacement);
    }

    private $replaceTagsOffsetCorrection = 0;

    private function replaceTag($offset, $length, $replacement)
    {
        $this->code =
            substr($this->code, 0, $offset + $this->replaceTagsOffsetCorrection) .
            $replacement .
            substr($this->code, $offset + $this->replaceTagsOffsetCorrection + $length);

        $this->replaceTagsOffsetCorrection += strlen($replacement) - $length;
    }

    // iterators branches

    private $iteratorsBranches = [];
    private $iteratorsBranchesIndex = [];
    private $currentIteratorBranch = 0;

    private function onCurrentBranch($iterator)
    {
        return $this->currentIteratorBranch && array_slice($iterator, 0, -1) == $this->getCurrentIteratorBranch();
    }

    private function getCurrentIteratorBranch()
    {
        if ($this->currentIteratorBranch) {
            return $this->iteratorsBranches[$this->currentIteratorBranch];
        }
    }

    private function getIteratorBranch($name)
    {
        if (isset($this->iteratorsBranchesIndex[$name])) {
            return $this->iteratorsBranches[$this->iteratorsBranchesIndex[$name]];
        }
    }

    private function addIteratorBranch($name)
    {
        $this->currentIteratorBranch++;
        $this->iteratorsBranchesIndex[$name] = $this->currentIteratorBranch;
        $this->addIteratorBranchNode($name);
    }

    private function addIteratorBranchNode($node)
    {
        $this->iteratorsBranches[$this->currentIteratorBranch][] = $node;
    }

    private function removeIteratorBranchLastNode()
    {
        $node = array_pop($this->iteratorsBranches[$this->currentIteratorBranch]);

        if (empty($this->iteratorsBranches[$this->currentIteratorBranch])) {
            unset($this->iteratorsBranchesIndex[$node]);
            $this->removeIteratorBranch();
        }
    }

    private function removeIteratorBranch()
    {
        unset($this->iteratorsBranches[$this->currentIteratorBranch]);
        $this->currentIteratorBranch--;
    }
}
