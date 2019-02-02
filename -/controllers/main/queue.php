<?php namespace ewma\controllers\main;

class Queue extends \Controller
{
    private $queueFile;

    private $queueFilePath;

    private $infoFilePath;

    private $commandFilePath;

    private function isProcessRunning($lockFile)
    {
        return !flock($lockFile, LOCK_EX | LOCK_NB);
    }

    public function handle()
    {
        $lockFilePath = $this->_protected($this->_instance() . '.lock');

        if (!file_exists($lockFilePath)) {
            write($lockFilePath);
        }

        $lockFile = fopen($lockFilePath, 'w');

        if ($this->isProcessRunning($lockFile)) {
            return 'running (' . dt() . ')';
        } else {
            $this->initFiles();

            awrite($this->infoFilePath, [
                'started at' => dt()
            ]);

            $this->loop();
        }
    }

    private function initFiles()
    {
        $this->queueFilePath = $this->_protected($this->_instance() . '.queue');

        if (!file_exists($this->queueFilePath)) {
            write($this->queueFilePath);
        }

        $this->queueFile = fopen($this->queueFilePath, 'r');

        $this->infoFilePath = $this->_protected($this->_instance() . '.info');

        if (!file_exists($this->infoFilePath)) {
            write($this->infoFilePath);
        }

        $this->commandFilePath = $this->_protected($this->_instance() . '.command');

        if (!file_exists($this->commandFilePath)) {
            write($this->commandFilePath, '');
        }
    }

    private function loop()
    {
        $debug = $this->data('debug');

        $pusher = pusher();

        $commandFileMTime = filemtime($this->commandFilePath);

        $i = 0;

        $totalJobsCount = 0;

        while (true) {
            $jobs = file($this->queueFilePath);

            if ($jobsCount = count($jobs)) {
                foreach ($jobs as $job) {
                    $jobData = _j($job);

                    list($ttl, $tab, $self, $event, $data) = $jobData;

                    if ($ttl > time()) {
                        $response = $pusher->triggerNow($tab, $self, $event, $data);
                    } else {
                        $response = 'expired';
                    }

                    if ($debug) {
                        $debugReport = [
                            '------------------ ' . dt() . ' ------------------',
                            'event: ' . $event,
                            'tab: ' . $tab,
                            'self: ' . $self,
                            'data: ' . j_($data),
                            'ttl: ' . (time() - $ttl),
                            'RESPONSE: ' . $response ?? '',
                            '---------------------------------------------------------'
                        ];

                        print PHP_EOL . implode(PHP_EOL, $debugReport) . PHP_EOL;
                    }
                }

                $totalJobsCount += $jobsCount;

                write($this->queueFilePath, '');

                if ($debug) {
                    print PHP_EOL . 'count: ' . count($jobs) . PHP_EOL;
                }
            }

            clearstatcache(true, $this->commandFilePath);

            if ($commandFileMTime != filemtime($this->commandFilePath)) {
                $command = read($this->commandFilePath);

                if ($command == 'stop') {
                    $info = aread($this->infoFilePath);

                    ra($info, [
                        'iteration'  => $i,
                        'jobs'       => $totalJobsCount,
                        'stopped at' => dt()
                    ]);

                    awrite($this->infoFilePath, $info);

                    break;
                }

                if ($command == 'getInfo') {
                    $info = aread($this->infoFilePath);

                    ra($info, [
                        'iteration' => $i,
                        'jobs'      => $totalJobsCount
                    ]);

                    awrite($this->infoFilePath, $info);
                }

                write($this->commandFilePath, '');
            }

            $i++;

            usleep(100000);
        }
    }

    public function stop()
    {
        $this->initFiles();

        write($this->commandFilePath, 'stop');
    }

    public function getInfo()
    {
        $this->initFiles();

        write($this->commandFilePath, 'getInfo');

        usleep(100000);

        return aread($this->infoFilePath);
    }

    public function create()
    {
        $queueFilePath = $this->_protected($this->_instance() . '.queue');

        if (!file_exists($queueFilePath)) {
            write($queueFilePath);

            return 'created queue: ' . $queueFilePath;
        } else {
            return 'queue ' . $queueFilePath . ' exists';
        }
    }

    public function add()
    {
        $ttl = 10;

        if ($this->dataHas('ttl')) {
            $ttl = $this->data('ttl');
        }

        $job = [
            time() + $ttl,
            $this->data('tab'),
            $this->data('self'),
            $this->data('event'),
            $this->data('data')
        ];

        $queueFilePath = $this->_protected($this->_instance() . '.queue');

        $queueFile = fopen($queueFilePath, 'a+');

        fwrite($queueFile, j_($job) . PHP_EOL);
        fclose($queueFile);
    }
}
