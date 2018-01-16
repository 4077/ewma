<?php namespace ewma\Response;

use ewma\App\App;
use ewma\Service\Service;

class Response extends Service
{
    /**
     * @var \Symfony\Component\HttpFoundation\Response
     */
    protected $proxy;

    protected function boot()
    {
        $this->proxy = \Symfony\Component\HttpFoundation\Response::create();
    }

    protected $services = ['app'];

    /**
     * @var App
     */
    public $app = App::class;

    //
    // css
    //

    private $cssFilesPaths;

    public function getCssFilesPaths($update = false)
    {
        // console
//        start_time('css');

        if (null === $this->cssFilesPaths || $update) {
            $this->cssFilesPaths = [];

            $nodes = $this->app->css->getNodes();

            if ($nodes) {
                $compilerSettings = $this->app->css->settings['compiler'];
                $combinerSettings = $this->app->css->settings['combiner'];

                $compilerTargetDir = $compilerSettings['dev_mode'] ? $compilerSettings['dev_mode_dir'] : $compilerSettings['dir'];

                $hasRecompiledNodes = false;
                $filesPaths = [];
                foreach ($nodes as $node) {
                    /**
                     * @var $node \ewma\Css\Node
                     */
                    if ($compilerSettings['dev_mode']) {
                        $targetFilePath = $node->getDevModeFilePath();
                    } else {
                        $targetFilePath = $this->app->paths->getFingerprintPath($node->getFingerprint());
                    }

                    if (!in_array($targetFilePath, $filesPaths)) {
                        $filesPaths[] = $targetFilePath;
                        $this->cssFilesPaths[] = $compilerTargetDir . '/' . $targetFilePath;
                    }

                    if ($compilerSettings['enabled']) {
                        if ($node->compile($compilerTargetDir, $targetFilePath, $compilerSettings)) {
                            $hasRecompiledNodes = true;
                        }
                    }
                }

                // комбинатор должен работать если включена перекомпиляция
                if ($hasRecompiledNodes && ($combinerSettings['enabled'] || $compilerSettings['enabled'])) {
//                    $this->app->css->combine($compilerTargetDir, $combinerSettings['dir'], $filesPaths);
                }

                // режим разработки несовместим с использование скомбинированных файлов // todo подумать об этом
                // из-за того, что в комбинированном файле пути к картинкам кодированные,
                // а в файлах сгенерированных в режиме разработки реальные
                if ($combinerSettings['use'] && !$compilerSettings['dev_mode']) {
                    $combinedPath = $this->app->css->getCombinedPath($filesPaths);

                    // принудительный запуск комбинатора, если файл еще не скомбинирован
                    if (!$combinerSettings['enabled'] && !file_exists(public_path($combinerSettings['dir'] . '/' . $combinedPath . '.css'))) {
                        $this->app->css->combine($compilerTargetDir, $combinerSettings['dir'], $filesPaths);
                    }

                    $this->cssFilesPaths = [$combinerSettings['dir'] . '/' . $combinedPath];
                }
            }
        }

        // console
//        $this->app->rootController->console(['css: ' . end_time('css')]);

        return $this->cssFilesPaths;
    }

    //
    // js
    //

    private $jsFilesPaths;

    public function getJsFilesPaths($update = false)
    {
        // console
//        start_time('js');

        if (null === $this->jsFilesPaths || $update) {
            $this->jsFilesPaths = [];

            $nodes = $this->app->js->getNodes();

            if ($nodes) {
                $compiler = $this->app->js->settings['compiler'];
                $combiner = $this->app->js->settings['combiner'];

                $compilerTargetDir = $compiler['dev_mode'] ? $compiler['dev_mode_dir'] : $compiler['dir'];

                $hasRecompiledNodes = false;
                $filesPaths = [];
                foreach ($nodes as $node) {
                    /**
                     * @var $node \ewma\Js\Node
                     */
                    if ($compiler['dev_mode']) {
                        $targetFilePath = $node->getFilePath();
                    } else {
                        $targetFilePath = $this->app->paths->getFingerprintPath($node->getFingerprint());
                    }

                    if (!in_array($targetFilePath, $filesPaths)) {
                        $filesPaths[] = $targetFilePath;
                        $this->jsFilesPaths[] = $compilerTargetDir . '/' . $targetFilePath;
                    }

                    if ($compiler['enabled']) {
                        if ($node->compile($compilerTargetDir, $targetFilePath, $compiler)) {
                            $hasRecompiledNodes = true;
                        }
                    }
                }

                // комбинатор должен работать если включена перекомпиляция
                // запускается только если был перекомпилирован хотя бы один узел
                if ($hasRecompiledNodes && ($combiner['enabled'] || $compiler['enabled'])) {
//                    $this->app->js->combine($compilerTargetDir, $combiner['dir'], $filesPaths);
                }

                if ($combiner['use']) {
                    $combinedPath = $this->app->js->getCombinedPath($filesPaths);

                    // принудительный запуск комбинатора, если файл еще не скомбинирован
                    if (!$combiner['enabled'] && !file_exists(public_path($combiner['dir'] . '/' . $combinedPath . '.js'))) {
                        $this->app->js->combine($compilerTargetDir, $combiner['dir'], $filesPaths);
                    }

                    $this->jsFilesPaths = [$combiner['dir'] . '/' . $combinedPath];
                }
            }
        }

        // console
//        $this->app->rootController->console(end_time('js'));

        return $this->jsFilesPaths;
    }

    //
    // cookies
    //

    private $cookies = [];

    public function cookie($name, $value = null, $timeout = false, $path = false)
    {
        if ($this->app->mode == App::REQUEST_MODE_XHR) {
            $this->cookies[] = [
                'name'    => $name,
                'value'   => $value,
                'expires' => $timeout ? $timeout + time() : time(),
                'path'    => $path ? $path : '/'
            ];
        }

        if ($this->app->mode == App::REQUEST_MODE_ROUTE) {
            setcookie($name, $value, $timeout ? $timeout + time() : time(), $path ? $path : '/');
        }
    }

    //
    // console
    //

    private $console = [];

    public function console($input)
    {
        $this->console[] = (array)l2a(func_get_args());
    }

    //
    // json
    //

    private $json = [];

    public function json($input)
    {
        $this->json[] = $input;
    }

    //
    // debug
    //

    private $counters = [];

    public function counter($name = false)
    {
        if (!isset($this->counters[$name])) {
            $this->counters[$name] = 0;
        }

        $this->counters[$name]++;
    }

    //
    //
    //

    public $redirect;

    public $redirectCode = 301;

    public function redirect($url = null, $code = 303)
    {
        $this->redirect = null === $url ? '/' : $url;
        $this->redirectCode = $code;
    }

    public $href;

    public function href($url = null) // xhr only
    {
        $this->href = null === $url ? '/' : $url;
    }

    public $reload;

    public function reload() // xhr only
    {
        $this->reload = true;
    }

    private function getInstructions()
    {
        return [
            'js'      => $this->app->js->getInstructions(),
            'json'    => $this->json,
            'cookies' => $this->cookies,
            'console' => $this->console
        ];
    }

    public $addViewsCommonInstructions = false;

    public function provideCommonJs()
    {
        $this->app->ewmaController->js('common');
    }

    private function addViewsCommonInstructions()
    {
        $this->app->ewmaController->js('common:ewma__common', false);
    }

    public function getAppData()
    {
        $this->app->events->trigger('beforeRenderAppData');

        if ($this->addViewsCommonInstructions) {
            $this->addViewsCommonInstructions();
        }

        if ($this->counters) {
            $this->console($this->counters);
        }

        $appData = [
            'url'          => $this->app->url,
            'js'           => [
                'version' => $this->app->js->settings['version'],
                'urls'    => $this->app->js->getUrls(),
                'paths'   => $this->getJsFilesPaths()
            ],
            'css'          => [
                'version' => $this->app->css->settings['version'],
                'urls'    => $this->app->css->getUrls(),
                'paths'   => $this->getCssFilesPaths()
            ],
            'instructions' => $this->getInstructions()
        ];

        $this->app->events->trigger('afterRenderAppData');

        return $appData;
    }

    //
    //
    //

    private $sent = false;

    public function send($response)
    {
        if (!$this->sent) {
            $this->app->events->trigger('app/terminate');

            $this->proxy->setContent($response);
            $this->proxy->send();

            $this->sent = true;
        }
    }

    public function sendJson($response)
    {
        if (is_array($response)) {
            $response = json_encode($response);
        }

        $this->proxy->headers->set('Content-type', 'application/json');

        $this->send($response);
    }

    public function sendAppResponse($response = null)
    {
        if (!$this->sent) {
            $this->app->events->trigger('app/terminate');

            if ($this->app->mode == App::REQUEST_MODE_CLI) {
                if (!is_scalar($response)) {
                    $response = j_($response);
                }

                $response = $response . PHP_EOL;

                fwrite(STDOUT, $response);
                fwrite(STDERR, false);

                exit(0);
            }

            if ($this->app->mode == App::REQUEST_MODE_XHR) {
                $appData = $this->getAppData();

                if (null !== $this->redirect) {
                    ra($appData, [
                        'redirect' => $this->redirect
                    ]);
                }

                if (null !== $this->href) { // xhr only
                    ra($appData, [
                        'href' => $this->href
                    ]);
                }

                if (null !== $this->reload) { // xhr only
                    ra($appData, [
                        'reload' => $this->reload
                    ]);
                }

                $this->proxy->headers->set('Content-Type', 'application/json');
                $this->proxy->setContent(json_encode($appData));
                $this->proxy->send();
            }

            if ($this->app->mode == App::REQUEST_MODE_ROUTE) {
                if (null !== $this->redirect) {
                    header('Location: ' . $this->redirect, true, $this->redirectCode); // todo ...

                    print $response;
//                $this->proxy->setStatusCode($this->redirectCode);
                } else {

                    $this->proxy->setContent($response);
                    $this->proxy->send();
                }
            }
        }
    }
}
