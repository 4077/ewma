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
        $this->settings = $this->app->ewmaController->d('~js', [
            'version'  => 0,
            'compiler' => [
                'enabled'      => true,
                'dir'          => 'js',
                'dev_mode'     => true,
                'dev_mode_dir' => 'js/dev',
                'minify'       => false
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
        $this->cache['nodes_mtimes'][$nodeId] = $mTime;
        $this->cacheUpdated = true;
    }

    public function cacheUpdateNodeMd5($fullCode, $md5)
    {
        $this->cache['nodes_md5'][$fullCode] = $md5;
        $this->cacheUpdated = true;
    }

    private function saveCache()
    {
        $this->app->cache->write('jsCompiler', $this->cache);
    }

    /**
     * @var Node[]
     */
    private $nodes = [];

    private $buffer = [];

    private $bufferEnabled = false;

    public function provide(Controller $controller, $relativePath)
    {
        $nodeId = $controller->_nodeId($relativePath);

        if (isset($this->nodes[$nodeId])) {
            return $this->nodes[$nodeId];
        } else {
            $node = new Node($controller, $relativePath, $nodeId);

            $this->nodes[$nodeId] = $node;

            if ($this->bufferEnabled) {
                $this->buffer[$nodeId] = $node;
            }

            return $node;
        }
    }

    public function startBuffer()
    {
        $this->bufferEnabled = true;
    }

    public function stopBuffer()
    {
        $output['nodes'] = [];

        foreach ($this->buffer as $node) {
            $output['nodes'][] = $this->bufferNode($node);
        }

        $instructions = [];

        foreach ($this->bufferInstructions as $instruction) {
            if ($instruction['type'] == 'jquery') {
                if ($instruction['builder']->callable) {
                    $instructions[] = [
                        'type' => 'raw',
                        'code' => $instruction['builder']->code
                    ];
                }
            } else {
                $instructions[] = $instruction;
            }
        }

        $output['instructions'] = $instructions;

        $this->bufferEnabled = false;
        $this->buffer = [];
        $this->bufferInstructions = [];

        return $output;
    }

    private function bufferNode(Node $node)
    {
        return [
            'node_path'     => $node->controller->__meta__->absPath,
            'relative_path' => $node->relativePath
        ];
    }

    public function unbuffer($buffer)
    {
        foreach ($buffer['nodes'] as $nodeBuffer) {
            $this->unbufferNode($nodeBuffer);
        }

        foreach ($buffer['instructions'] as $instruction) {
            $this->addInstruction($instruction);
        }
    }

    private function unbufferNode($nodeBuffer)
    {
        $controller = $this->app->c()->n($nodeBuffer['node_path']);

        $node = new Node($controller, $nodeBuffer['relative_path']);

        if (!isset($this->nodes[$node->id])) {
            $this->nodes[$node->id] = $node;
        }
    }

    private $urls = [];

    public function provideUrl($url)
    {
        if (!in_array($url, $this->urls)) {
            $this->urls[] = $url;
        }
    }

    private function addInstruction($instruction)
    {
        $this->instructions[] = $instruction;

        if ($this->bufferEnabled) {
            $this->bufferInstructions[] = $instruction;
        }
    }

    private $instructions = [];

    private $bufferInstructions = [];

    public function addCall(Controller $controller, $relativeNodePath, $callString, $callArgs)
    {
        if ($callString == '' || substr($callString, 0, 1) == '.') {
            $callString = $controller->_nodeId($relativeNodePath) . $callString;
        }

        $this->addInstruction([
                                  'type' => 'call',
                                  'data' => [
                                      'method' => $callString,
                                      'args'   => $callArgs
                                  ]
                              ]);
    }

    public function addRaw($code)
    {
        $this->addInstruction([
                                  'type' => 'raw',
                                  'code' => $code
                              ]);
    }

    public function addJqueryBuilder(Controller $controller, $relativeNodePath, $selector)
    {
        $nodeId = $controller->_nodeId($relativeNodePath);

        $jqueryBuilder = new JqueryBuilder($selector, $nodeId);

        $this->addInstruction([
                                  'type'    => 'jquery',
                                  'builder' => $jqueryBuilder
                              ]);

        return $jqueryBuilder;
    }

    public function getUrls()
    {
        return $this->urls;
    }

    /**
     * @return Node[]
     */
    public function getNodes()
    {
        return $this->nodes;
    }

    //

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

//    public function getFilesPaths()
//    {
//        $output = [];
//
//        $compilerSettings = $this->app->js->settings['compiler'];
//
//        $compilerTargetDir = $compilerSettings['dev_mode'] ? $compilerSettings['dev_mode_dir'] : $compilerSettings['dir'];
//
//        $filesPaths = [];
//        foreach ($this->nodes as $nodeCode => $node) {
//            if ($compilerSettings['dev_mode']) {
//                $targetFilePath = $node->getFilePath();
//            } else {
//                $targetFilePath = $this->app->paths->getFingerprintPath($node->getFingerprint());
//            }
//
//            if (!in_array($targetFilePath, $filesPaths)) {
//                $filesPaths[] = $targetFilePath;
//
//                $output[] = $compilerTargetDir . '/' . $targetFilePath;
//            }
//
//            if ($compilerSettings['enabled']) {
//                $node->compile($compilerTargetDir, $targetFilePath, $compilerSettings);
//            }
//        }
//
//        return $output;
//    }

    public function getHrefs()
    {
        $output = [];

        $compilerSettings = $this->settings['compiler'];

        $compilerTargetDir = $compilerSettings['dev_mode'] ? $compilerSettings['dev_mode_dir'] : $compilerSettings['dir'];

        $addedFilesPaths = [];

        foreach ($this->nodes as $nodeCode => $node) {
            if ($compilerSettings['dev_mode']) {
                $targetFilePath = $node->getFilePath();
            } else {
                $targetFilePath = $this->app->paths->getFingerprintPath($node->getFingerprint());
            }

            if ($compilerSettings['enabled']) {
                $node->compile($compilerTargetDir, $targetFilePath, $compilerSettings);
            }

            if (!in_array($targetFilePath, $addedFilesPaths)) {
                $addedFilesPaths[] = $targetFilePath;

                $md5 = $this->cache['nodes_md5'][$nodeCode] ?? false;

                $output[] = '/' . $compilerTargetDir . '/' . $targetFilePath . '.js' . ($md5 ? '?' . $md5 : '');
            }
        }

        return $output;
    }
}
