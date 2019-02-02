<?php namespace ewma\Process;

use ewma\Service\Service;

class ProcessDispatcher extends Service
{
    private $pidsDir;

    private $locksDir;

    private $outputsDir;

    private $logController;

    public function boot()
    {
        mdir($this->pidsDir = abs_path('proc/pids'));
        mdir($this->locksDir = abs_path('proc/locks'));
        mdir($this->outputsDir = abs_path('proc/outputs'));

        $this->logController = appc('\ewma~process');
    }

    public function create($path, $data = [])
    {
        $process = Process::create($this);

        $process->setCall([$path, $data]);

        return $process;
    }

    public function open($pid)
    {
        return Process::open($pid, $this);
    }

    public function getPidsDir()
    {
        return $this->pidsDir;
    }

    public function getPidDir($pid)
    {
        return $this->pidsDir . '/' . $pid;
    }

    public function getLocksDir()
    {
        return $this->locksDir;
    }

    public function getLockFilePath($lockName)
    {
        return abs_path($this->locksDir . '/' . $lockName . '.lock');
    }

    public function getLockFile($lockName)
    {
        return fopen($this->getLockFilePath($lockName), 'w');
    }

    public function getOutputFilePath($outputName)
    {
        return abs_path($this->outputsDir . '/' . $outputName . '.json');
    }

    public function getPids()
    {
        return array_diff(scandir($this->pidsDir), ['.', '..']);
    }

    public function getNewPid()
    {
        $pids = $this->getPids();

        do {
            $newPid = rand(11111, 99999);
        } while (in_array($newPid, $pids));

        return $newPid;
    }

    public function log($content)
    {
        $this->logController->log($content);
    }
}
