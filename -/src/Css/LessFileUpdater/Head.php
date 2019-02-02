<?php namespace ewma\Css\LessFileUpdater;

class Head
{
    private $filePath;

    public function __construct($filePath, $content)
    {
        $this->filePath = $filePath;

        $this->parse($content);
    }

    private $originalContent;

    private $originalImportList;

    private $originalNodeId;

    private function parse($content)
    {
        $this->originalContent = $content;

        if (preg_match_all('/@import \'(.*)\';/', $content, $pathsMatch)) {
            $this->originalImportList = $pathsMatch[1];
        }

        if (preg_match('/@__nodeId__: ~"(.*)";/', $content, $nodeIdMatch)) {
            $this->originalNodeId = $nodeIdMatch[1];
        }
    }

    private $importList = [];

    public function setImportList($importList)
    {
        $app = app();

        $lessFilePath = $this->filePath;

        $relativePathsList = [];
        foreach ($importList as $importPath) {
            $relativePath = $app->paths->getRelativePath($importPath, $lessFilePath);
            $relativePathsList[] = $relativePath . '.less';
        }

        $this->importList = $relativePathsList;
    }

    private $nodeId;

    public function setNodeId($nodeId)
    {
        $this->nodeId = $nodeId;
    }

    private function isChanged()
    {
        if (merge($this->originalImportList, [], true) != merge($this->importList, [], true)) {
            return true;
        }

        if ($this->originalNodeId != $this->nodeId) {
            return true;
        }
    }

    public function compile()
    {
        if ($this->isChanged()) {
            $output = [];

            $importStrings = $this->getImportStrings();
            if ($importStrings) {
                $output[] = implode(';' . PHP_EOL, $importStrings) . ';';
            }

            if ($this->nodeId) {
                $output[] = '@__nodeId__: ~"' . $this->nodeId . '";';
            }

            if ($output) {
                return
                    '// head {' .
                    PHP_EOL .
                    implode(PHP_EOL . PHP_EOL, $output) .
                    PHP_EOL .
                    '// }';
            } else {
                return '';
            }

        } else {
            return $this->originalContent;
        }
    }

    private function getImportStrings()
    {
        $importStrings = [];
        foreach ($this->importList as $importPath) {
            $importStrings[] = '@import "' . $importPath . '"';
        }

        return $importStrings;
    }
}
