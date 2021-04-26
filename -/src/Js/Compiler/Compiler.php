<?php namespace ewma\Js\Compiler;

use ewma\Js\Minifier\Minifier;

class Compiler
{
    private $targetDir;

    private $targetFilePath;

    private $settings;

    public function __construct($targetDir, $targetFilePath, $settings)
    {
        $this->targetDir = $targetDir;
        $this->targetFilePath = $targetFilePath;
        $this->settings = $settings;
    }

    private $sourceFilePath;

    private $sourceType;

    public function setSource($filePath, $type)
    {
        $this->sourceFilePath = $filePath;
        $this->sourceType = $type;
    }

    private $lessVars = [];

    public function setLessVars($vars)
    {
        $this->lessVars = $vars;
    }

    public function compile()
    {
        $sourceFileContent = read(abs_path($this->sourceFilePath) . '.' . $this->sourceType);

        $js = '';

        if ($this->sourceType == 'js') {
            $js = $sourceFileContent;
        }

        if ($this->settings['minify'] && !$this->settings['dev_mode']) {
            $js = Minifier::minify($js);
        }

        $filePath = public_path($this->targetDir, $this->targetFilePath . '.js');

        write($filePath, $js);

        return $filePath;
    }
}
