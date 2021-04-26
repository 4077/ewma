<?php namespace ewma\Css;

use ewma\App\App;
use ewma\Service\Service;
use ewma\Controllers\Controller;

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

    public function cacheUpdateNodeMTime($fullCode, $mTime)
    {
        $this->cache['nodes_mtimes'][$fullCode] = $mTime;
        $this->cacheUpdated = true;
    }

    public function cacheUpdateNodeMd5($fullCode, $md5)
    {
        $this->cache['nodes_md5'][$fullCode] = $md5;
        $this->cacheUpdated = true;
    }

    private function saveCache()
    {
        $this->app->cache->write('cssCompiler', $this->cache);
    }

    /**
     * @var Node[]
     */
    private $nodes = [];

    private $buffer = [];

    private $bufferEnabled = false;

    public function provide(Controller $nodeController, $relativePath, $instance)
    {
        $nodeCode = $nodeController->_nodeId($relativePath) . ($instance ? '|' . $instance : '');

        if (isset($this->nodes[$nodeCode])) {
            return $this->nodes[$nodeCode];
        } else {
            $node = new Node($nodeController, $relativePath, $instance);

            $this->nodes[$nodeCode] = $node;

            if ($this->bufferEnabled) {
                $this->buffer[$nodeCode] = $node;
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
        $output = [];

        foreach ($this->buffer as $node) {
            $output[] = $this->bufferNode($node);
        }

        $this->bufferEnabled = false;
        $this->buffer = [];

        return $output;
    }

    private function bufferNode(Node $node)
    {
        return [
            'node_path'     => $node->controller->__meta__->absPath,
            'relative_path' => $node->relativePath,
            'instance'      => $node->instance,
            'import_paths'  => $node->importPaths,
            'import_ids'    => $node->importIds,
            'vars'          => $node->vars
        ];
    }

    public function unbuffer($buffer)
    {
        foreach ($buffer as $nodeBuffer) {
            $this->unbufferNode($nodeBuffer);
        }
    }

    private function unbufferNode($nodeBuffer)
    {
        $controller = $this->app->c()->n($nodeBuffer['node_path']);

        $node = new Node($controller, $nodeBuffer['relative_path'], $nodeBuffer['instance']);

        $node->importPaths = $nodeBuffer['import_paths'];
        $node->importIds = $nodeBuffer['import_ids'];
        $node->vars = $nodeBuffer['vars'];

        if (!isset($this->nodes[$node->code])) {
            $this->nodes[$node->code] = $node;
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

//    /**
//     * @return Node[]
//     */
//    public function getNodes()
//    {
//        return $this->nodes;
//    }

    public function getHrefs()
    {
        $output = [];

        $compilerSettings = $this->settings['compiler'];

        $compilerTargetDir = $compilerSettings['dev_mode'] ? $compilerSettings['dev_mode_dir'] : $compilerSettings['dir'];

        $addedFilesPaths = [];

        foreach ($this->nodes as $nodeCode => $node) {
            $nodeFullCode = $node->getFullCode();

            if ($compilerSettings['dev_mode']) {
                $targetFilePath = $node->getDevModeFilePath();
            } else {
                $targetFilePath = $this->app->paths->getFingerprintPath($node->getFingerprint());
            }

            if ($compilerSettings['enabled']) {
                $node->compile($compilerTargetDir, $targetFilePath, $compilerSettings);
            }

            if (!in_array($targetFilePath, $addedFilesPaths)) {
                $addedFilesPaths[] = $targetFilePath;

                $md5 = $this->cache['nodes_md5'][$nodeFullCode] ?? false;

                $output[] = '/' . $compilerTargetDir . '/' . $targetFilePath . '.css' . ($md5 ? '?' . $md5 : '');
            }
        }

        return $output;
    }
}
