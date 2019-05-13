<?php namespace ewma\Process;

use ewma\App\App;
use ewma\Service\Service;

declare(ticks=1);

class AppProcess extends Service
{
    protected $services = ['app', 'dispatcher'];

    /**
     * @var App
     */
    public $app = App::class;

    /**
     * @var \ewma\Process\ProcessDispatcher
     */
    public $dispatcher = \ewma\Process\ProcessDispatcher::class;

    //
    //
    //

    private $pid;

    private $pidDir;

    private $xpid;

    private $configFilePath;

    private $xpidFilePath;

    private $callFilePath;

    private $signalFilePath;

    private $inputFilePath;

    private $outputFilePath;

    private $errorsFilePath;

    private $progressFilePath;

    public function boot()
    {
        if ($pid = $this->app->getPid()) {
            $this->pidDir = $this->dispatcher->getPidDir($pid);

            $this->pid = $pid;
            $this->xpid = read($this->pidDir . '/xpid');

            $this->initFiles();
        }
    }

    private function initFiles()
    {
        $pidDir = $this->pidDir;

        $this->xpidFilePath = $this->dispatcher->getXPidFilePath($this->xpid);

        $this->callFilePath = $pidDir . '/call.json';
        $this->configFilePath = $pidDir . '/config.json';
        $this->inputFilePath = $pidDir . '/input.json';
        $this->outputFilePath = $pidDir . '/output.json';
        $this->errorsFilePath = $pidDir . '/errors.json';
        $this->progressFilePath = $pidDir . '/progress.json';
        $this->signalFilePath = $pidDir . '/signal';
    }

    public function run()
    {
        $this->readInput();

        pcntl_signal(SIGTERM, function () {
            $this->dispatcher->open($this->pid)->break();
            $this->dispatcher->log('SIGTERM');
        });

        $this->app->c()->_call($this->getCall())->perform();
    }

    public function getCall()
    {
        return jread($this->callFilePath);
    }

    public function getPid()
    {
        return $this->pid;
    }

    public function getXPid()
    {
        return $this->xpid;
    }

    //
    // read config/signal
    //

    private $configFileMTime;

    private $config;

    private function getConfig($path = false)
    {
        if (file_exists($this->configFilePath)) {
            clearstatcache(true, $this->configFilePath);

            if ($this->configFileMTime != filemtime($this->configFilePath)) {
                $this->config = jread($this->configFilePath);

                $this->configFileMTime = filemtime($this->configFileMTime);
            }
        }

        return ap($this->config, $path);
    }

    private $signalFileMTime;

    private $signal;

    private function getSignal()
    {
        if (file_exists($this->signalFilePath)) {
            clearstatcache(true, $this->signalFilePath);

            if ($this->signalFileMTime != filemtime($this->signalFilePath)) {
                $this->signal = read($this->signalFilePath);

                $this->signalFileMTime = filemtime($this->signalFilePath);
            }
        }

        return $this->signal;
    }

    //
    // handle/terminate
    //

    public function handleIteration($sleepMs = false)
    {
        $signal = $this->getSignal();

        if ($signal != Signals::NONE) {
            if ($signal == Signals::PAUSE) {
                while (true) {
                    $onPauseSignal = $this->getSignal();

                    if ($onPauseSignal == Signals::UPDATE_INPUT) {
                        $this->readInput();

                        $this->signal = Signals::PAUSE;
                    }

                    if ($onPauseSignal == Signals::RESUME) {
                        break;
                    }

                    if ($onPauseSignal == Signals::BREAK) {
                        return true;
                    }

                    usleep(500000);
                }
            }

            if ($signal == Signals::UPDATE_INPUT) {
                $this->readInput();
            }

            if ($signal == Signals::BREAK) {
                return true;
            }

            $this->signal = Signals::NONE;
        }

        if ($sleepMs) {
            usleep($sleepMs * 1000);
        }
    }

    public function terminate()
    {
        $xpidData = jread($this->xpidFilePath);
        $xpidData['terminated'] = time();
        jwrite($this->xpidFilePath, $xpidData);

        delete_dir($this->pidDir);

        $this->dispatcher->log('terminate pid=' . $this->pid);
    }

    //
    // READ input
    //

    private $input;

    private function readInput()
    {
        $this->input = jread($this->inputFilePath);
    }

    public function input($path = false)
    {
        return ap($this->input, $path);
    }

    //
    // READ/WRITE output
    //

    private $output;

    private function writeOutput($data)
    {
        jwrite($this->outputFilePath, $data);

        $outputs = $this->getConfig('outputs');

        foreach ($outputs as $output) {
            jwrite($output, $data);
        }

        $xpidData = jread($this->xpidFilePath);
        $xpidData['output'] = $data;
        jwrite($this->xpidFilePath, $xpidData);
    }

    public function readOutput($path = false)
    {
        $output = jread($this->outputFilePath);

        return ap($output, $path);
    }

    public function output($data)
    {
        $this->writeOutput($data);
    }

    public function aa($data)
    {
        $output = $this->readOutput();

        aa($output, $data);

        $this->writeOutput($output);
    }

    public function ra($data)
    {
        $output = $this->readOutput();

        ra($output, $data);

        $this->writeOutput($output);
    }

    public function rr($data)
    {
        $this->writeOutput($data);
    }

    //
    // WRITE errors
    //

    private $errors;

    private function writeErrors()
    {
        jwrite($this->outputFilePath, $this->output);

        $errors = $this->getConfig('errors');

        foreach ($errors as $error) {
            jwrite($error, $this->errors);
        }

        $xpidData = jread($this->xpidFilePath);
        $xpidData['errors'] = $this->errors;
        jwrite($this->xpidFilePath, $xpidData);
    }

    public function error($message)
    {
        $this->errors[] = $message;

        $this->dispatcher->log('ADD ERROR: ' . $message);

        $this->writeErrors();
    }

    //
    // WRITE progress
    //

    public function progress($current, $total = null, $comment = false, $data = [])
    {
        if ($total > 0) {
            $percent = $current / $total * 100;
        } else {
            $percent = 0;
        }

        $progressData = [
            'current'      => $current,
            'total'        => $total,
            'percent'      => $percent,
            'percent_ceil' => ceil($percent),
            'comment'      => $comment,
            'data'         => $data
        ];

        jwrite($this->progressFilePath, $progressData);

        $xpidData = jread($this->xpidFilePath);
        $xpidData['progress'] = $progressData;
        jwrite($this->xpidFilePath, $xpidData);
    }
}
