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

    private $queryLogSettings;

    public function startQueryLog()
    {
        $this->queryLogSettings = &appd('\ewma~queryLog');

        $enabled = ap($this->queryLogSettings, 'enabled');

        if (null === $enabled) {
            appd('\ewma~queryLog', [
                'enabled'  => false,
                'settings' => [
                    'max_requests' => 10000,
                    'slow_log'     => [
                        'enabled'      => false,
                        'threshold_ms' => 100
                    ]
                ]
            ]);
        }

        if ($enabled) {
            \DB::enableQueryLog();

            return true;
        }
    }

    public function writeQueryLog($requestLogString, $requestDuration)
    {
        $slowLogEnabled = ap($this->queryLogSettings, 'settings/slow_log/enabled');
        $slowLogThreshold = ap($this->queryLogSettings, 'settings/slow_log/threshold_ms');

        //
        //
        //

        $queryLog = \DB::getQueryLog();

        $rows = [];
        $slowRows = [];

        $totalSqlDuration = 0;

        foreach ($queryLog as $n => $queryLogItem) {
            $query = vsprintf(str_replace(['%', '?'], ['%%', '\'%s\''], $queryLogItem['query']), $queryLogItem['bindings']);

            $queryData = [
                'statement' => $queryLogItem['query'],
                'query'     => $query,
                'duration'  => $queryLogItem['time']
            ];

            $queryDuration = $queryLogItem['time'];

            $totalSqlDuration += $queryDuration;

            $rows[] = j_($queryData) . PHP_EOL;

            if ($slowLogEnabled && $queryDuration > $slowLogThreshold) {
                $slowRows[] = '['.str_pad(\ewma\Data\Formats\Numeric::parseDecimal($queryDuration, 2), 8, ' ', STR_PAD_LEFT) . ' ms] ' . $query . PHP_EOL;
            }
        }

        $sqlDurationRate = $totalSqlDuration / (float)$requestDuration * 100;

        array_unshift(
            $rows,
            'REQUEST............: ' . $requestLogString . PHP_EOL,
            'request duration...: ' . \ewma\Data\Formats\Numeric::parseDecimal($requestDuration, 2) . PHP_EOL,
            'sql duration.......: ' . \ewma\Data\Formats\Numeric::parseDecimal($totalSqlDuration, 2) . PHP_EOL,
            'sql duration rate..: ' . \ewma\Data\Formats\Numeric::parseDecimal($sqlDurationRate, 2) . '%' . PHP_EOL,
            'queries............: ' . count($rows) . PHP_EOL,
            PHP_EOL
        );

        //

        $rows[] = PHP_EOL;

        $lastRequestFilePath = abs_path('logs/mysql/last_request.sql');
        $filePath = abs_path('logs/mysql/requests', round(microtime(true) * 1000) . '_' . $this->app->session->getPublicKey() . '_' . count($queryLog) . '.sql');

        write($lastRequestFilePath, $rows);
        write($filePath, $rows);

        $filesPaths = glob(abs_path('logs/mysql/requests/*.sql'));

        sort($filesPaths);

        $filesCount = count($filesPaths);

        $maxRequests = ap($this->queryLogSettings, 'settings/max_requests');

        if ($filesCount > $maxRequests) {
            $rmFilesPaths = array_slice($filesPaths, 0, $filesCount - $maxRequests);

            foreach ($rmFilesPaths as $rmFilePath) {
                unlink($rmFilePath);
            }
        }

        // slow

        if ($slowLogEnabled) {
            $slowLogFilePath = abs_path('logs/mysql/slow.sql');

            if (!file_exists($slowLogFilePath)) {
                write($slowLogFilePath);
            }

            $slowLogFile = fopen($slowLogFilePath, 'a');

            foreach ($slowRows as $slowRow) {
                fwrite($slowLogFile, $slowRow);
            }

            fclose($slowLogFile);
        }
    }
}
