<?php namespace ewma\controllers\main;

class Backup extends \Controller
{
    public function run()
    {
        $envId = $this->data('env_id');

        if (!$envId || $this->_env($envId)) {
            $this->log('run');

            $this->cleanOld();

            $targetDir = $this->getTargetDir();

            $this->log('target dir: ' . $targetDir);

            $this->writeComment($targetDir);
            $this->makeDump($targetDir);
            $this->writeInfo($targetDir);

            if ($this->data('files')) {
                $this->makeFilesArchive($targetDir);
            }

            return 'backup to ' . $targetDir;
        }
    }

    private function getTypeDir()
    {
        $type = $this->data('type') ?: 'manual';

        $mntPath = dataSets()->get('ewma/backup::mnt_path');

        return path('mnt/', $mntPath, $this->_env(), $type);
    }

    private function getTargetDir()
    {
        $targetDir = '/' . path($this->getTypeDir(), date('Y-m-d_H-i-s', time()));

        exec('mkdir -p ' . $targetDir);

        return $targetDir;
    }

    private function cleanOld()
    {
        if ($ttlMins = $this->getTtlMins()) {
            $this->log('cleanOld (ttl: ' . $ttlMins . ' mins)');

            $typeDir = '/' . $this->getTypeDir();

            if (strlen($typeDir) > 10) {
                // find . -type d -mmin +$((60*24*537)) -exec ls -al {} \;

                exec('find ' . $typeDir . ' -type d -mmin +' . $ttlMins . ' -exec rm -rf {} \;');
            }
        }
    }

    private function writeComment($targetDir)
    {
        if ($comment = $this->data('comment')) {
            write($targetDir . '/comment.txt', $comment);
        }
    }

    private function writeInfo($targetDir)
    {
        $lines = [];

        $commit = $this->c('\ewma\dev~:exec', ['command' => 'git log -1 --pretty=\'%H %B\'']);

        if (isset($commit[0])) {
            $lines[] = 'commit: ' . $commit[0];
        }

        $branch = $this->c('\ewma\dev~:exec', ['command' => 'git rev-parse --abbrev-ref HEAD']);

        if (isset($branch[0])) {
            $lines[] = 'branch: ' . $branch[0];
        }

        write($targetDir . '/info.txt', implode(PHP_EOL, $lines));
    }

    private function makeDump($targetDir)
    {
        $user = app()->getConfig('databases/default/user');
        $pass = app()->getConfig('databases/default/pass');
        $name = app()->getConfig('databases/default/name');

        $this->log('make dump...');

        start_time();

        exec('mysqldump -u ' . $user . ' -p' . $pass . ' ' . $name . ' > ' . $targetDir . '/' . $name . '.sql');

        $this->log('...done in ' . end_time());
    }

    private function makeFilesArchive($targetDir)
    {
        exec('nice -19 tar -czvf ' . $targetDir . '/files.tar.gz -C ' . abs_path() . ' .');
    }

    public function getTtlMins()
    {
        if ($ttl = $this->data('ttl')) {
            $k = [
                'm' => 1,
                'h' => 60,
                'd' => 60 * 24,
                'w' => 60 * 24 * 7,
                'M' => 60 * 24 * 30,
                'Y' => 60 * 24 * 365
            ];

            if (preg_match('/^(\d+)([mhdwMY]?)$/', $ttl, $match)) {
                $value = $match[1];
                $units = $match[2];

                return $value * $k[$units];
            }
        }
    }
}
