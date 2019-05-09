<?php namespace ewma\Process;

use ewma\Service\Service;

class ProcessDispatcher extends Service
{
    private $pidsDir;

    private $locksDir;

    private $tmpOutputsDir;

    private $xpidsDir;

    private $xpidsMapFilePath;

    private $logController;

    public function boot()
    {
        mdir($this->pidsDir = abs_path('proc/pids'));
        mdir($this->locksDir = abs_path('proc/locks'));
        mdir($this->tmpOutputsDir = abs_path('proc/tmp'));

        mdir($this->xpidsDir = public_path('proc'));

        $this->xpidsMapFilePath = abs_path('proc/xpids.json');

        if (!file_exists($this->xpidsMapFilePath)) {
            write($this->xpidsMapFilePath);
        }

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
        if (file_exists($this->getPidDir($pid))) {
            return Process::open($pid, $this);
        }
    }

    public function openByXpid($xpid)
    {
        $pid = $this->getPidByXPid($xpid);

        return $this->open($pid);
    }

    public function getPidByXPid($xpid)
    {
        $map = jread($this->xpidsMapFilePath);

        return $map[$xpid] ?? false;
    }

    public function xpidsMapAdd($pid, $xpid)
    {
        $map = jread($this->xpidsMapFilePath);
        $map[$xpid] = $pid;
        jwrite($this->xpidsMapFilePath, $map);
    }

    public function xpidsMapRemove($xpid)
    {
        $map = jread($this->xpidsMapFilePath);

        if (isset($map[$xpid])) {
            unset($map[$xpid]);
        }

        jwrite($this->xpidsMapFilePath, $map);
    }

    public function removeXPid($xpid)
    {
        $xpidFilePath = $this->getXPidFilePath($xpid);

        if (file_exists($xpidFilePath)) {
            unlink($xpidFilePath);
        }

        $this->xpidsMapRemove($xpid);
    }

    public function clearTerminatedXPids()
    {
        $xpids = $this->getXPids();

        $now = time();

        foreach ($xpids as $xpid) {
            $data = jread($this->getXPidFilePath($xpid));

            if ($terminatedAt = $data['terminated'] ?? false) {
                if ($now - $terminatedAt > 60) {
                    $this->removeXPid($xpid);
                }
            }
        }
    }

    public function getPidsDir()
    {
        return $this->pidsDir;
    }

    public function getPidDir($pid)
    {
        return $this->pidsDir . '/' . $pid;
    }

    public function getXPidFilePath($xpid)
    {
        return public_path('proc', $xpid . '.json');
    }

    public function getTmpOutputFilePath($outputName)
    {
        return abs_path($this->tmpOutputsDir . '/' . $outputName . '.json');
    }

    public function getLockFilePath($lockName)
    {
        return abs_path($this->locksDir . '/' . $lockName . '.lock');
    }

    public function getLockFile($lockName)
    {
        return fopen($this->getLockFilePath($lockName), 'w');
    }

    //
    // generate pid/xpid
    //

    private function getPids()
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

    private function getXPids()
    {
        $xpidsFiles = array_diff(scandir($this->xpidsDir), ['.', '..']);

        $xpids = array_map(function ($fileName) {
            return str_replace('.json', '', $fileName);
        }, $xpidsFiles);

        return $xpids;
    }

    public function getNewXPid()
    {
        $xpids = $this->getXPids();

        do {
            $newXPid = k();
        } while (in_array($newXPid, $xpids));

        return $newXPid;
    }

    //
    // log
    //

    public function log($content)
    {
        $this->logController->log($content);
    }

    /*

    proc/locks/{md5}

    proc/tmp/{md5}

    proc/pids/{pid}/call.json
    proc/pids/{pid}/config.json
    proc/pids/{pid}/input.json
    proc/pids/{pid}/output.json
    proc/pids/{pid}/signal
    proc/pids/{pid}/xpid



    public_html/proc/{xpid}.json


    */
}


























