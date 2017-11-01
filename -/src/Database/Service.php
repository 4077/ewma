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

    protected function boot()
    {
        $database = new Database;

        foreach ($this->app->getConfig('databases') as $name => $config) {
            $database->addConnection([
                                         'driver'    => isset($config['driver']) ? $config['driver'] : 'mysql',
                                         'host'      => $config['host'],
                                         'database'  => $config['name'],
                                         'username'  => $config['user'],
                                         'password'  => $config['pass'],
                                         'charset'   => isset($config['charset']) ? $config['charset'] : 'utf8',
                                         'collation' => isset($config['collation']) ? $config['collation'] : 'utf8_unicode_ci',
                                         'prefix'    => '',
                                     ], $name);
        }

        $database->getDatabaseManager()->setDefaultConnection($this->app->getConfig('default_db'));
        $database->setEventDispatcher(new Dispatcher(new Container));
        $database->setAsGlobal();
        $database->bootEloquent();
    }
}
