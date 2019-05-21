<?php namespace ewma\controllers\main;

class Git extends \Controller
{
    public function getBranch()
    {
        return trim(shell_exec('git branch | grep \* | cut -d \' \' -f2'));
    }

    public function add()
    {
        if ($paths = l2a($this->data('paths'))) {
            foreach ($paths as $path) {
                exec('git add ' . $path);
            }
        }
    }

    public function commit()
    {
        $message = $this->data('message');

        $command = 'git commit -m "' . str_replace('"', '\"', $message) . '"';

        $process = proc_open(
            $command,
            [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']],
            $pipes
        );

        $out = [];

        if (is_resource($process)) {
            $this->log('>>> ' . $command);

            while ($line = fgets($pipes[2]) or $line = fgets($pipes[1])) {
                $this->log('    ' . rtrim($line));

                $out[] = rtrim($line);
            }

            proc_close($process);
        }

        return $out;
    }

    public function push()
    {
        $repo = $this->data('repo') ?? $this->data('repository') ?? 'origin';
        $branch = $this->getBranch();

        $push = false;

        if ($ifBranch = $this->data('if_branch')) {
            if ($branch === $ifBranch) {
                $push = true;
            }
        } else {
            $push = true;
        }

        if ($push) {
            exec('git push ' . $repo . ' ' . $branch, $out);

            return $out;
        }
    }
}
