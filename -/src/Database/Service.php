<?php namespace ewma\Database;

use ewma\App\App;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

class Service extends \ewma\Service\Service
{
    protected $services = ['app'];

    /**
     * @var App
     */
    public $app = App::class;

    /**
     * @var \ewma\Database\Database
     */
    public $manager;

    protected function boot()
    {
        $this->manager = new Database;

        foreach ($this->app->getConfig('databases') as $name => $config) {
            $this->addConnection($name, $config);
        }

        $this->manager->getDatabaseManager()->setDefaultConnection($this->app->getConfig('default_db'));
        $this->manager->setEventDispatcher(new Dispatcher(new Container));
        $this->manager->setAsGlobal();
        $this->manager->bootEloquent();
    }

    public function addConnection($name, $config)
    {
        $this->manager->addConnection([
                                          'driver'    => $config['driver'] ?? 'mysql',
                                          'host'      => $config['host'],
                                          'database'  => $config['name'],
                                          'username'  => $config['user'],
                                          'password'  => $config['pass'],
                                          'charset'   => $config['charset'] ?? 'utf8',
                                          'collation' => $config['collation'] ?? 'utf8_unicode_ci',
                                          'prefix'    => '',
                                      ], $name);
    }

    public function startQueryLog()
    {
        \DB::enableQueryLog();
    }

    public function getQueryLog()
    {
        $queryLog = \DB::getQueryLog();

        $queries = [];

        foreach ($queryLog as $n => $query) {
            $queries[] = vsprintf(str_replace(['%', '?'], ['%%', '\'%s\''], $query['query']), $query['bindings']) . ';' . PHP_EOL;
        }

        return $queries;
    }
}
