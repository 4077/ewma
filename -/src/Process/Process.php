<?php namespace ewma\Process;

class Process
{
    private $dispatcher;

    private $pid;

    private $call;

    private $callFilePath;

    private $signalFilePath;

    private $signalDataFilePath;

    private $inputFilePath;

    private $outputFilePath;

    private $progressFilePath;

    public function __construct(ProcessDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public static function create(ProcessDispatcher $dispatcher)
    {
        $process = new self($dispatcher);

        $process->setPid($dispatcher->getNewPid());
        $process->init();

        return $process;
    }

    public static function open($pid, ProcessDispatcher $dispatcher)
    {
        $process = new self($dispatcher);

        $process->setPid($pid);
        $process->init();

        return $process;
    }

    public function setCall($call)
    {
        if (null === $this->call) {
            $this->call = $call;
        }
    }

    private $lockName;

    public function lock($instance)
    {
        $this->lockName = md5($instance);

        return $this;
    }

    public function pathLock($instance = false)
    {
        $path = $this->call[0];

        $this->lockName = jmd5([$path, $instance]);

        return $this;
    }

    private function hasLock($lockFile)
    {
        return !flock($lockFile, LOCK_EX | LOCK_NB);
    }

    private $run;

    public function run($input = [])
    {
        if (null === $this->run) {
            $locked = false;

            if ($this->lockName) {
                $lockFile = $this->dispatcher->getLockFile($this->lockName);

                if ($this->hasLock($lockFile)) {
                    $locked = true;
                }
            }

            if ($locked) {
                $pidDir = $this->dispatcher->getPidDir($this->pid);

                delete_dir($pidDir);

                $this->dispatcher->log('lock ' . $this->lockName);

                return false;
            } else {
                jwrite($this->callFilePath, $this->call);
                jwrite($this->inputFilePath, $input);

                $processHandlerData = [
                    'pid'  => $this->pid,
                    'call' => $this->call
                ];

                $command = 'nohup ./cli -j \'' . str_replace("'", "'\''", j_(['processHandler:handle', $processHandlerData])) . '\' >> ~/proc.log 2>&1 &';

                $this->dispatcher->log('PROC ' . $command);

                $cwd = getcwd();
                chdir(app()->root);
                exec($command);
                chdir($cwd);

                $this->run = true;
            }

            return $this;
        }
    }

    public function wait()
    {
        $outputFilePath = $this->dispatcher->getOutputFilePath(k());

        $this->addOutput($outputFilePath);

        $pidDir = $this->dispatcher->getPidDir($this->pid);

        $this->dispatcher->log('WAIT BEGIN pid=' . $this->pid);

        while (file_exists($pidDir)) {
            usleep(100000);
        }

        $this->dispatcher->log('WAIT END pid=' . $this->pid);

        $output = jread($outputFilePath);

        if (file_exists($outputFilePath)) {
            unlink($outputFilePath);
        }

        return $output; // todo test
    }

    public function init()
    {
        $this->initFiles();
    }

    private function initFiles()
    {
        $processDir = $this->getProcessDir();

        $this->callFilePath = $processDir . '/call.json';
        $this->signalFilePath = $processDir . '/signal';
        $this->signalDataFilePath = $processDir . '/signal-data.json';
        $this->inputFilePath = $processDir . '/input.json';
        $this->outputFilePath = $processDir . '/output.json';
        $this->progressFilePath = $processDir . '/progress.json';

        if (!file_exists($this->signalFilePath)) {
            write($this->signalFilePath);
        }

        if (!file_exists($this->signalDataFilePath)) {
            write($this->signalDataFilePath);
        }
    }

    private function getProcessDir()
    {
        return $this->dispatcher->getPidDir($this->pid);
    }

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

    public function pause()
    {
        $this->signal(Signals::PAUSE);
    }

    public function resume()
    {
        $this->signal(Signals::RESUME);
    }

    public function break()
    {
        $this->signal(Signals::BREAK);
    }

    public function addOutput($outputFilePath)
    {
        $this->signal(Signals::ADD_OUTPUT, $outputFilePath);
    }

    private function signal($signal, $data = null)
    {
        write($this->signalFilePath, $signal);
        jwrite($this->signalDataFilePath, $data);
    }

    //
    // READ output
    //

    private $output;

    private function updateOutput()
    {
        $this->output = jread($this->outputFilePath);
    }

    public function output($path = false)
    {
        $this->updateOutput();

        return ap($this->output, $path);
    }

    //
    // WRITE input
    //

    private $input;

    private function updateInput()
    {
        jwrite($this->inputFilePath, $this->input);

        $this->signal(Signals::UPDATE);
    }

    public function input($data)
    {
        $this->input = $data;

        $this->updateInput();
    }

    public function aa($path, $value)
    {
        $node = &ap($this->input, $path);

        aa($node, $value);

        $this->updateInput();
    }

    public function ra($path, $value)
    {
        $node = &ap($this->input, $path);

        ra($node, $value);

        $this->updateInput();
    }

    public function rr($path, $value)
    {
        $node = &ap($this->input, $path);

        $node = $value;

        $this->updateInput();
    }

    //
    //
    //

    public function getProgressUrl()
    {

    }

    public function getOutputUrl()
    {

    }
}
