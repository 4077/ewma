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

//    //
//    // css
//    //
//
//    private $cssFilesPaths;
//
//    public function getCssFilesPaths() // todo del
//    {
//        $nodes = $this->app->css->getNodes();
//
//        if ($nodes) {
//            $sequence = array_keys($nodes);
//
//            $compilerSettings = $this->app->css->settings['compiler'];
//
//            $compilerTargetDir = $compilerSettings['dev_mode'] ? $compilerSettings['dev_mode_dir'] : $compilerSettings['dir'];
//
//            $filesPaths = [];
//            foreach ($nodes as $nodeCode => $node) {
//                if (!isset($this->cssFilesPaths[$nodeCode])) {
//                    if ($compilerSettings['dev_mode']) {
//                        $targetFilePath = $node->getDevModeFilePath();
//                    } else {
//                        $targetFilePath = $this->app->paths->getFingerprintPath($node->getFingerprint());
//                    }
//
//                    if (!in_array($targetFilePath, $filesPaths)) {
//                        $filesPaths[] = $targetFilePath;
//
//                        $this->cssFilesPaths[$nodeCode] = $compilerTargetDir . '/' . $targetFilePath;
//                    }
//
//                    if ($compilerSettings['enabled']) {
//                        $node->compile($compilerTargetDir, $targetFilePath, $compilerSettings);
//                    }
//                }
//            }
//
//            $this->cssFilesPaths = map($this->cssFilesPaths, $sequence);
//        }
//
//        return $this->cssFilesPaths;
//    }

    //
    // js
    //

    private $jsFilesPaths;

    public function getJsFilesPaths() // todo del
    {
        $nodes = $this->app->js->getNodes();

        if ($nodes) {
            $sequence = array_keys($nodes);

            $compilerSettings = $this->app->js->settings['compiler'];

            $compilerTargetDir = $compilerSettings['dev_mode'] ? $compilerSettings['dev_mode_dir'] : $compilerSettings['dir'];

            $filesPaths = [];
            foreach ($nodes as $nodeCode => $node) {
                if (!isset($this->jsFilesPaths[$nodeCode])) {
                    if ($compilerSettings['dev_mode']) {
                        $targetFilePath = $node->getFilePath();
                    } else {
                        $targetFilePath = $this->app->paths->getFingerprintPath($node->getFingerprint());
                    }

                    if (!in_array($targetFilePath, $filesPaths)) {
                        $filesPaths[] = $targetFilePath;
                        $this->jsFilesPaths[$nodeCode] = $compilerTargetDir . '/' . $targetFilePath;
                    }

                    if ($compilerSettings['enabled']) {
                        $node->compile($compilerTargetDir, $targetFilePath, $compilerSettings);
                    }
                }
            }

            $this->jsFilesPaths = map($this->jsFilesPaths, $sequence);
        }

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
        $this->console[] = $input;
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
                'hrefs'   => $this->app->js->getHrefs()
            ],
            'css'          => [
                'version' => $this->app->css->settings['version'],
                'urls'    => $this->app->css->getUrls(),
                'hrefs'   => $this->app->css->getHrefs(),
            ],
            'instructions' => [
                'js'      => $this->app->js->getInstructions(),
                'cookies' => $this->cookies,
                'console' => $this->console
            ]
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

    public function json($response)
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
