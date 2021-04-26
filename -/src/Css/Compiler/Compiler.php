<?php namespace ewma\Css\Compiler;

use ewma\Css\Minifier\Minifier;

class Compiler
{
    private $targetDir;

    private $targetFilePath;

    private $settings;

    private $varsFingerprint;

    public function __construct($targetDir, $targetFilePath, $settings, $varsFingerprint = false)
    {
        $this->targetDir = $targetDir;
        $this->targetFilePath = $targetFilePath;
        $this->settings = $settings;
        $this->varsFingerprint = $varsFingerprint;
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

    public function setInstance($instance)
    {
        $this->lessVars['__instance__'] = $instance;
    }

    public function compile()
    {
        $sourceFileContent = read(abs_path($this->sourceFilePath) . '.' . $this->sourceType);

        $css = '';

        if ($this->sourceType == 'css') {
            $css = $sourceFileContent;
        }

        if ($this->sourceType == 'less') {
            try {
                $lessc = new \lessc;

                $lessc->setImportDir(path_slice(abs_path($this->sourceFilePath), 0, -1));

                $css = $lessc->parse($sourceFileContent, $this->lessVars);

                $css = $this->removeInstanceSpaces($css);
            } catch (\Less_Exception_Parser $e) {
                appc()->console($e->getMessage() . ' (' . $this->sourceFilePath . ')');
            }
        }

        $css = $this->rewriteUrlsAndCopyFiles($css, $this->sourceFilePath, $this->targetFilePath);

        if ($this->settings['minify'] && !$this->settings['dev_mode']) {
            $css = Minifier::minify($css);
        }

        $filePath = public_path($this->targetDir, $this->targetFilePath . '.css');

        write($filePath, $css);

        return $filePath;
    }

    private function removeInstanceSpaces($css)
    {
        if (preg_match_all('/instance=\'(.*)\'/U', $css, $instanceMatches)) {
            $instance = $instanceMatches[1][0];

            $css = str_replace('instance=\'' . $instance . '\'', 'instance=\'' . str_replace(' ', '', $instance) . '\'', $css);
        }

        return $css;
    }

    private function rewriteUrlsAndCopyFiles($css, $sourceFilePath, $targetFilePath)
    {
        if (preg_match_all('/url\((.*)\)/Us', $css, $urlsMatch)) {
            $urls = $urlsMatch[1];

            $sourceDirPath = path_slice($sourceFilePath, 0, -1);
            $targetDirPath = path_slice($targetFilePath, 0, -1);

            $app = app();

            foreach ($urls as $url) {
                if (
                    false === strpos($url, 'http://') &&
                    false === strpos($url, 'https://') &&
                    false === strpos($url, 'data:')
                ) {
                    $url = str_replace(['"', '\''], '', $url);
                    $fileDirPath = path_slice($url, 0, -1);

                    $sourceFileAbsPath = abs_path($sourceDirPath, $url);

                    if (is_file($sourceFileAbsPath)) {
                        if ($this->settings['dev_mode']) {
                            $targetDirAbsPath = app()->paths->normalizePath(
                                public_path($this->targetDir, $targetDirPath, $fileDirPath)
                            );

                            mdir($targetDirAbsPath);

                            $targetFileAbsPath = public_path($this->targetDir, $targetDirPath, $url);

                            copy($sourceFileAbsPath, $targetFileAbsPath);
                        } else {
                            $fingerprintPath = $app->paths->getFingerprintPath(
                                md5_file($sourceFileAbsPath),
                                sha1_file($sourceFileAbsPath)
                            );

                            $dirPath = path_slice($fingerprintPath, 0, -1);
                            $fileName = path_slice($fingerprintPath, -1);

                            $targetDirAbsPath = $app->paths->normalizePath(
                                public_path($this->targetDir, $dirPath)
                            );

                            mdir($targetDirAbsPath);

                            $pathInfo = pathinfo($sourceFileAbsPath);

                            $targetFilePath = path($this->targetDir, $dirPath, $fileName) . ($pathInfo['extension'] ? '.' . $pathInfo['extension'] : '');

                            $targetFileAbsPath = public_path($this->targetDir, $dirPath, $fileName) . ($pathInfo['extension'] ? '.' . $pathInfo['extension'] : '');

                            copy($sourceFileAbsPath, $targetFileAbsPath);

                            $css = preg_replace('/url\([\'|"]?' . str_replace('/', '\/', $url) . '[\'|"]?\)/Us', 'url(\'/' . $targetFilePath . '\')', $css);
                        }
                    }
                }
            }
        }

        return $css;
    }
}
