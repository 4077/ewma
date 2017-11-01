<?php namespace ewma\Js;

use ewma\App\App;
use ewma\Service\Service;
use ewma\Controllers\Controller;
use ewma\Js\Minifier\Minifier;

class Js extends Service
{
    protected $services = ['app'];

    /**
     * @var App
     */
    public $app = App::class;

    public $settings;

    public function boot()
    {
        $this->settings = $this->app->ewmaController->d('js~', [
            'version'  => 0,
            'compiler' => [
                'enabled'      => true,
                'dir'          => 'js',
                'dev_mode'     => true,
                'dev_mode_dir' => 'js/dev',
                'minify'       => false
            ],
            'combiner' => [
                'enabled' => true,
                'use'     => false,
                'dir'     => 'js/combined',
                'minify'  => false
            ]
        ]);

        $this->cache = $this->app->cache->read('jsCompiler');

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
        $this->app->cache->write('jsCompiler', $this->cache);
    }

    //
    // методы для Controller
    //

    private $nodes;

    /**
     * Обеспечить загрузку js-файла в браузер.
     * Js-файл будет сгенериван из js-узла,
     * располагающегося по адресу $relativePath относительно $controller.
     * Less-узел обладает приоритетом при выборе компилятором.
     *
     * @param Controller $controller
     * @param            $relativePath
     *
     * @return Node
     */
    public function provide(Controller $controller, $relativePath)
    {
        $nodeId = $controller->_nodeId($relativePath);

        if (isset($this->nodes[$nodeId])) {
            return $this->nodes[$nodeId];
        } else {
            $node = new Node($controller, $relativePath, $nodeId);

            $this->nodes[$nodeId] = $node;

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

    private $instructions = [];

    public function addCall(Controller $controller, $relativeNodePath, $callString, $callArgs)
    {
        if ($callString == '' || substr($callString, 0, 1) == '.') {
            $callString = $controller->_nodeId($relativeNodePath) . $callString;
        }

        $this->instructions[] = [
            'type' => 'call',
            'data' => [
                'method' => $callString,
                'args'   => $callArgs
            ]
        ];
    }

    public function addRaw($code)
    {
        $this->instructions[] = [
            'type' => 'raw',
            'code' => $code
        ];
    }

    public function addJqueryBuilder(Controller $controller, $relativeNodePath, $selector)
    {
        $nodeId = $controller->_nodeId($relativeNodePath);

        $jqueryBuilder = new JqueryBuilder($selector, $nodeId);

        $this->instructions[] = [
            'type'    => 'jquery',
            'builder' => $jqueryBuilder
        ];

        return $jqueryBuilder;
    }

    /**
     * методы для \Ewma\Response\Response
     */

    public function getUrls()
    {
        return $this->urls;
    }

    public function getNodes()
    {
        return $this->nodes;
    }

    public function getInstructions()
    {
        $output = [];

        foreach ($this->instructions as $instruction) {
            if ($instruction['type'] == 'jquery') {
                if ($instruction['builder']->callable) {
                    $output[] = [
                        'type' => 'raw',
                        'code' => $instruction['builder']->code
                    ];
                }
            } else {
                $output[] = $instruction;
            }
        }

        return $output;
    }

    public function combine($sourceDir, $targetDir, $filesPaths)
    {
        $combiner = $this->settings['combiner'];

        $combinedJsArray = [];
        foreach ($filesPaths as $filePath) {
            $combinedJsArray[] = read(abs_path($sourceDir, $filePath . '.js'));
        }

        $combinedJs = implode(';', $combinedJsArray);

        if ($combiner['minify']) {
            $combinedJs = Minifier::minify($combinedJs);
        }

        $targetFilePath = public_path($targetDir, $this->getCombinedPath($filesPaths) . '.js');

        // файл перезаписывается только если изменилось содержание (иначе браузер будет качать его каждый раз)
        $currentFileContent = read($targetFilePath);
        if ($currentFileContent !== $combinedJs) {
            write($targetFilePath, $combinedJs);
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
