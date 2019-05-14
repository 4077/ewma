<?php namespace ewma\controllers\main;

class Composer extends \Controller
{
    public function install()
    {
        $command = 'composer --ansi install';

        $process = proc_open(
            $command,
            [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']],
            $pipes
        );

        if (is_resource($process)) {
            $this->log('>>> ' . $command);

            while ($line = fgets($pipes[2]) or $line = fgets($pipes[1])) {
                $this->log('    ' . rtrim($line));
            }

            proc_close($process);
        }
    }

    public function update()
    {
        $command = 'composer --ansi update';

        $process = proc_open(
            $command,
            [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']],
            $pipes
        );

        if (is_resource($process)) {
            $this->log('>>> ' . $command);

            while ($line = fgets($pipes[2]) or $line = fgets($pipes[1])) {
                $this->log('    ' . rtrim($line));
            }

            proc_close($process);
        }
    }

    public function require()
    {
        $command = 'composer --ansi require ' . $this->data('package') . ':"' . $this->data('version') . "'";

        $process = proc_open(
            $command,
            [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']],
            $pipes
        );

        if (is_resource($process)) {
            $this->log('>>> ' . $command);

            while ($line = fgets($pipes[2]) or $line = fgets($pipes[1])) {
                $this->log('    ' . rtrim($line));
            }

            proc_close($process);
        }
    }
}
