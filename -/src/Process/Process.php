<?php namespace ewma\Process;

class Process
{
    private $dispatcher;

    private $pid;

    private $pidDir;

    private $xpid;

    private $call;

    private $user;

    private $configFilePath;

    private $callFilePath;

    private $signalFilePath;

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
        $process->setXPid($dispatcher->getNewXPid());

        $process->pidDir = $dispatcher->getPidDir($process->pid);

        write($process->pidDir . '/xpid', $process->xpid);

        jwrite($dispatcher->getXPidFilePath($process->xpid), [
            'output'   => false,
            'progress' => false
        ]);

        $dispatcher->xpidsMapAdd($process->pid, $process->xpid);

        $process->initFiles($process->pidDir);

        return $process;
    }

    public static function open($pid, ProcessDispatcher $dispatcher)
    {
        $process = new self($dispatcher);

        $process->pidDir = $dispatcher->getPidDir($pid);

        $process->setPid($pid);
        $process->setXPid(read($process->pidDir . '/xpid'));

        $process->initFiles($process->pidDir);

        return $process;
    }

    private function initFiles($pidDir)
    {
        $this->configFilePath = $pidDir . '/config.json';
        $this->callFilePath = $pidDir . '/call.json';
        $this->inputFilePath = $pidDir . '/input.json';
        $this->outputFilePath = $pidDir . '/output.json';
        $this->signalFilePath = $pidDir . '/signal';
        $this->progressFilePath = $pidDir . '/progress.json';

        if (!file_exists($this->signalFilePath)) {
            write($this->signalFilePath, Signals::NONE);
        }

        if (!file_exists($this->configFilePath)) {
            jwrite($this->configFilePath, [
                'outputs' => []
            ]);
        }
    }

    public function setCall($call)
    {
        if (null === $this->call) {
            $this->call = $call;
        }
    }

    public function user(\ewma\access\models\User $user)
    {
        if (null === $this->user) {
            $this->user = $user;
        }

        return $this;
    }

    //
    // locks
    //

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

    //
    // run/wait
    //

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
                $this->dispatcher->removeXPid($this->xpid);
                delete_dir($this->pidDir);

//                $this->dispatcher->log('LOCK ' . $this->lockName);

                return false;
            } else {
                jwrite($this->callFilePath, $this->call);
                jwrite($this->inputFilePath, $input);

                $processHandlerData = [
                    app()->getConfig('name') => $this->pid,
                    'call'                   => $this->call
                ];

                $userArg = '';
                if ($this->user) {
                    $userArg = ' -u \'' . $this->user->login . '\'';
                }

                $command = 'nohup ./cli' . $userArg . ' -j \'' . str_replace("'", "'\''", j_(['processHandler:handle', $processHandlerData])) . '\' >> ' . app()->root . 'logs/proc.log 2>&1 &';

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
        $tmpOutputFilePath = $this->dispatcher->getTmpOutputFilePath(k());

        $this->addOutput($tmpOutputFilePath);

        $this->dispatcher->log('WAIT BEGIN pid=' . $this->pid);

        while (file_exists($this->pidDir)) {
            usleep(100000);
        }

        $this->dispatcher->log('WAIT END pid=' . $this->pid);

        $output = jread($tmpOutputFilePath);

        if (file_exists($tmpOutputFilePath)) {
            unlink($tmpOutputFilePath);
        }

        return $output;
    }

    //
    // pids
    //

    public function setPid($pid)
    {
        if (null === $this->pid) {
            $this->pid = $pid;
        }
    }

    public function setXPid($xpid)
    {
        if (null === $this->xpid) {
            $this->xpid = $xpid;
        }
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
    // signals
    //

    public function pause()
    {
        $this->signal(Signals::PAUSE);
    }

    public function resume()
    {
        $this->signal(Signals::RESUME);
    }

    public function togglePause()
    {
        if ($this->isPaused()) {
            $this->resume();

            return false;
        } else {
            $this->pause();

            return true;
        }
    }

    public function break()
    {
        $this->signal(Signals::BREAK);
    }

    private function signal($signal)
    {
        $this->dispatcher->log('SIGNAL ' . $signal);

        write($this->signalFilePath, $signal);
    }

    public function isPaused()
    {
        $signal = read($this->signalFilePath);

        return $signal == Signals::PAUSE;
    }

    //
    // add output
    //

    public function addOutput($outputFilePath)
    {
        $config = jread($this->configFilePath);

        merge($config['outputs'], $outputFilePath);

        jwrite($this->configFilePath, $config);
    }

    //
    // READ output
    //

    private $output;

    private function readOutput()
    {
        $this->output = jread($this->outputFilePath);
    }

    public function _output($path = false)
    {
        $this->readOutput();

        return ap($this->output, $path);
    }

    //
    // WRITE input
    //

    private $input;

    private function writeInput()
    {
        jwrite($this->inputFilePath, $this->input);

        $this->signal(Signals::UPDATE_INPUT);
    }

    public function input_($path, $value)
    {
        ap($this->input, $path, $value);

        $this->writeInput();
    }

    public function inputAA($data)
    {
        aa($this->input, $data);

        $this->writeInput();
    }

    public function inputRA($data)
    {
        ra($this->input, $data);

        $this->writeInput();
    }

    public function inputRR($data)
    {
        $this->input = $data;

        $this->writeInput();
    }
}
