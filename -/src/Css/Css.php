<?php namespace ewma\Css;

use ewma\App\App;
use ewma\Service\Service;
use ewma\Controllers\Controller;
use ewma\Css\Minifier\Minifier;

class Css extends Service
{
    protected $services = ['app'];

    /**
     * @var App
     */
    public $app = App::class;

    public $settings;

    public function boot()
    {
        $this->settings = $this->app->ewmaController->d('~css', [
            'version'  => 0,
            'compiler' => [
                'enabled'      => true,
                'dir'          => 'css',
                'dev_mode'     => true,
                'dev_mode_dir' => 'css/dev',
                'minify'       => false
            ],
            'combiner' => [
                'enabled' => true,
                'use'     => false,
                'dir'     => 'css/combined',
                'minify'  => false
            ]
        ]);

        $this->cache = $this->app->cache->read('cssCompiler');

        $this->app->events->bind('app/terminate', function () {
            if ($this->cacheUpdated) {
                $this->saveCache();
            }
        });
    }

    public $cache;
    private $cacheUpdated;

    public function cacheUpdateNodeMTime($nodeId, $mTime)
    {
        $this->cache['nodes_m_times'][$nodeId] = $mTime;
        $this->cacheUpdated = true;
    }

    private function saveCache()
    {
        $this->app->cache->write('cssCompiler', $this->cache);
    }

    private $nodes;

    /**
     * Обеспечить загрузку css-файла в браузер.
     * Css-файл будет сгенериван из less- или css-узла,
     * располагающегося по адресу $relativePath относительно $controller.
     * Less-узел обладает приоритетом при выборе компилятором.
     *
     * @param Controller $controller
     * @param            $relativePath
     *
     * @return Node
     */
    public function provide(Controller $controller, $relativePath, $instance)
    {
        $nodeId = $controller->_nodeId($relativePath);

        $nodeCode = $nodeId . ($instance ? '|' . $instance : '');

        if (isset($this->nodes[$nodeCode])) {
            return $this->nodes[$nodeCode];
        } else {
            $node = new Node($controller, $relativePath, $nodeId, $instance);

            $this->nodes[$nodeCode] = $node;

            return $node;
        }
    }

    private $urls = [];

    public function provideUrl($url)
    {
        if (!in_array($url, $this->urls)) {
            $this->urls[] = $url;
        }
    }

    public function getUrls()
    {
        return $this->urls;
    }

    public function getNodes()
    {
        return $this->nodes;
    }

    public function combine($sourceDir, $targetDir, $filesPaths)
    {
        $combiner = $this->settings['combiner'];

        $combinedCss = '';
        foreach ($filesPaths as $filePath) {
            $combinedCss .= read(public_path($sourceDir, $filePath . '.css'));
        }

        $targetFilePath = public_path($targetDir, $this->getCombinedPath($filesPaths) . '.css');

        if ($combiner['minify']) {
            $combinedCss = Minifier::minify($combinedCss);
        }

        // файл перезаписывается только если изменилось содержание (иначе браузер будет качать его каждый раз)
        $currentFileContent = read($targetFilePath);
        if ($currentFileContent !== $combinedCss) {
            write($targetFilePath, $combinedCss);
        }
    }

    public function getCombinedPath($filesPaths)
    {
        $fingerprint = md5(implode(',', $filesPaths));

        $dirPath = implode('/', str_split(substr($fingerprint, 0, 8), 2));
        $fileName = substr($fingerprint, 7, 8);

        return $dirPath . '/' . $fileName;
    }
}
