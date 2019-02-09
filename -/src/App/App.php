<?php namespace ewma\App;

use ewma\Service\Service;

class App extends Service
{
    /**
     * @var self
     */
    public static $instance;

    /**
     * @param null $root
     *
     * @return \ewma\App\App
     * @throws \Exception
     */
    public static function getInstance($root = null, $mode = null)
    {
        if (null === static::$instance) {
            if (null == $root) {
                throw new \Exception('Get App instance without root');
            } else {
                $app = new self;

                $app->root = force_r_slash($root); // todo rm slash
                $app->publicRoot = $app->root . 'public_html';

                if (null !== $mode) {
                    $app->mode = $mode;
                }

                static::$instance = $app;
                static::$instance->__register__();
            }
        }

        return static::$instance;
    }

    protected $services = [
        'cache',
        'request',
        'response',
        'html',
        'css',
        'js',
        'access',
        'database',
        'events',
        'sessionEvents',
        'storageEvents',
        'session',
        'storage',
        'paths',
        'modules',
        'configs',
        'controllers',
        'views',
        'process',
        'processDispatcher'
    ];

    /**
     * @var \ewma\Cache\Cache
     */
    public $cache = \ewma\Cache\Cache::class;

    /**
     * @var \ewma\Request\Request
     */
    public $request = \ewma\Request\Request::class;

    /**
     * @var \ewma\Response\Response
     */
    public $response = \ewma\Response\Response::class;

    /**
     * @var \ewma\Html\Html
     */
    public $html = \ewma\Html\Html::class;

    /**
     * @var \ewma\Css\Css
     */
    public $css = \ewma\Css\Css::class;

    /**
     * @var \ewma\Js\Js
     */
    public $js = \ewma\Js\Js::class;

    /**
     * @var \ewma\Access\Access
     */
    public $access = \ewma\Access\Access::class;

    /**
     * @var \ewma\Database\Service
     */
    public $database = \ewma\Database\Service::class;

    /**
     * @var \ewma\Events\Events
     */
    public $events = \ewma\Events\Events::class;

    /**
     * @var \ewma\SessionEvents\SessionEvents
     */
    public $sessionEvents = \ewma\SessionEvents\SessionEvents::class;

    /**
     * @var \ewma\StorageEvents\StorageEvents
     */
    public $storageEvents = \ewma\StorageEvents\StorageEvents::class;

    /**
     * @var \ewma\Session\Session
     */
    public $session = \ewma\Session\Session::class;

    /**
     * @var \ewma\Storage\Storage
     */
    public $storage = \ewma\Storage\Storage::class;

    /**
     * @var \ewma\Paths\Paths
     */
    public $paths = \ewma\Paths\Paths::class;

    /**
     * @var \ewma\Modules\Modules
     */
    public $modules = \ewma\Modules\Modules::class;

    /**
     * @var \ewma\Configs\Configs
     */
    public $configs = \ewma\Configs\Configs::class;

    /**
     * @var \ewma\Controllers\Controllers
     */
    public $controllers = \ewma\Controllers\Controllers::class;

    /**
     * @var \ewma\Views\Views
     */
    public $views = \ewma\Views\Views::class;

    /**
     * @var \ewma\Process\AppProcess
     */
    public $process = \ewma\Process\AppProcess::class;

    /**
     * @var \ewma\Process\ProcessDispatcher
     */
    public $processDispatcher = \ewma\Process\ProcessDispatcher::class;

    //
    //
    //

    private $config = [];

    protected function boot()
    {
        mb_internal_encoding("UTF-8");

        #1 настройки кеша (обязательно перед #2)
        $this->cache->setDir(abs_path('cache'));
        $this->cache->setMode(\ewma\Cache\Cache::PHP);

        $rootModule = $this->modules->getRootModule();

        #2 загрузка конфига приложения (обязательно перед #3)
        $this->config = $rootModule->config;

        #3 загрузка бд (обязательно перед #6)
        $this->database->up();

        #4 загрузка резолвера путей (обязательно перед #6)
        $this->paths->resolver->up();

        #5 контроллеры корневого модуля, модуля фреймворка и контроллер для выполнения запроса (обязательно перед #6)
        $this->rootController = $rootModule->getController();
        $this->ewmaController = $this->modules->getByNamespace('ewma')->getController();
        $this->requestHandlerController = $this->c('requestHandler');

        $this->ewmaController->c('~events:bindDispatchers');

        #6 авторизация
        if ($this->mode != self::REQUEST_MODE_CLI) {
            $this->access->auth->process();
        }

        #7
        $this->storage->up();
        $this->session->up();

        #8 остальное
        $this->setEnv();

        $this->host = $this->request->getHost();
        $this->scheme = $this->request->getScheme();

        $sslHosts = l2a($this->getConfig('ssl_hosts'));

        if (in_array($this->host, $sslHosts)) {
            $this->url = force_r_slash('https://' . $this->host);
        } else {
            $this->url = force_r_slash($this->request->getSchemeAndHttpHost());
        }

        setlocale(LC_ALL, $this->getConfig('locale') ?? 'ru_RU.utf8');

        if (!headers_sent()) {
            header('Content-type: text/html; charset=utf-8'); // todo response
        }
    }

    /**
     * @var \ewma\Controllers\Controller
     */
    public $rootController;

    /**
     * @var \ewma\Controllers\Controller
     */
    public $requestHandlerController;

    /**
     * @var \ewma\Controllers\Controller
     */
    public $ewmaController;

    const REQUEST_MODE_ROUTE = 0;
    const REQUEST_MODE_XHR = 1;
    const REQUEST_MODE_CLI = 2;

    public $mode;
    public $route;

    public $root;
    public $publicRoot;
    public $scheme;
    public $url;
    public $host;
    public $tab;

    public function getConfig($path = '')
    {
        return ap($this->config, $path);
    }

    // env

    private $env;

    public function setEnv()
    {
        if (null === $this->env) {
            $this->env = $this->getConfig('env');
        }

        return $this->env;
    }

    public function getEnv()
    {
        return $this->env;
    }

    // pid

    private $pid;

    public function setPid($pid)
    {
        if (null === $this->pid) {
            $this->pid = $pid;
        }
    }

    public function getPid()
    {
        return $this->pid;
    }

    /**
     * @return \ewma\Controllers\Controller
     */
    public function c()
    {
        $args = func_get_args();

        if ($args) {
            $output = call_user_func_array([$this->rootController, 'c'], $args);
        } else {
            $output = $this->rootController;
        }

        return $output;
    }
}
