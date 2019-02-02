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

    private $callFilePath;

    private $signalDataFilePath;

    private $signalFilePath;

    private $inputFilePath;

    private $outputFilePath;

    private $progressFilePath;

    public function boot()
    {
        if ($pid = $this->app->getPid()) {
            $this->pid = $pid;

            $this->init();
        }
    }

    public function get()
    {
        return $this;
    }

    public function run()
    {
        $this->updateInput();

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

    private function init()
    {
        $this->initFiles();
    }

    private function initFiles()
    {
        $pidDir = $this->dispatcher->getPidDir($this->pid);

        $this->callFilePath = $pidDir . '/call.json';
        $this->inputFilePath = $pidDir . '/input.json';
        $this->signalFilePath = $pidDir . '/signal';
        $this->signalDataFilePath = $pidDir . '/signal-data.json';
        $this->outputFilePath = $pidDir . '/output.json';
        $this->progressFilePath = $pidDir . '/progress.json';
    }

    private $outputs = [];

    private function addOutput($filePath)
    {
        $this->dispatcher->log('ADD OUTPUT ' . $filePath);

        merge($this->outputs, $filePath);
    }

    public function getPid()
    {
        return $this->pid;
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

    private function getSignalData()
    {
        return jread($this->signalDataFilePath);
    }

    public function handleIteration($sleepMs = false)
    {
        $signal = $this->getSignal();

        if ($signal != Signals::NONE) {
            if ($signal == Signals::PAUSE) {
                while (true) {
                    $onPauseSignal = $this->getSignal();

                    if ($onPauseSignal == Signals::UPDATE) {
                        $this->updateInput();
                    }

                    if ($onPauseSignal == Signals::RESUME) {
                        break;
                    }

                    if ($onPauseSignal == Signals::BREAK) {
                        return true;
                    }

                    if ($onPauseSignal == Signals::ADD_OUTPUT) {
                        $signalData = $this->getSignalData();

                        $this->addOutput($signalData);
                    }

                    usleep(500000);
                }
            }

            if ($signal == Signals::UPDATE) {
                $this->updateInput();
            }

            if ($signal == Signals::BREAK) {
                return true;
            }

            if ($signal == Signals::ADD_OUTPUT) {
                $signalData = $this->getSignalData();

                $this->addOutput($signalData);
            }

            $this->signal = Signals::NONE;
        }

        if ($sleepMs) {
            usleep($sleepMs * 1000);
        }
    }

    public function progress($current, $total = null, $comment = false)
    {
        jwrite($this->progressFilePath, [
            'current' => $current,
            'total'   => $total,
            'comment' => $comment
        ]);
    }

    public function terminate()
    {
        $pidDir = $this->dispatcher->getPidDir($this->pid);

        delete_dir($pidDir);

        $this->dispatcher->log('terminate pid=' . $this->pid);
    }

    //
    // READ input
    //

    private $input;

    private function updateInput()
    {
        $this->input = jread($this->inputFilePath);
    }

    public function input($path = false)
    {
        return ap($this->input, $path);
    }

    //
    // WRITE output
    //

    private $output;

    private function updateOutput()
    {
        jwrite($this->outputFilePath, $this->output);

        foreach ($this->outputs as $output) {
            jwrite($output, $this->output);
        }
    }

    public function output($data)
    {
        $this->output = $data;

        $this->updateOutput();
    }

    public function aa($path, $value)
    {
        $node = &ap($this->output, $path);

        aa($node, $value);

        $this->updateOutput();
    }

    public function ra($path, $value)
    {
        $node = &ap($this->output, $path);

        ra($node, $value);

        $this->updateOutput();
    }

    public function rr($path, $value)
    {
        $node = &ap($this->output, $path);

        $node = $value;

        $this->updateOutput();
    }
}
