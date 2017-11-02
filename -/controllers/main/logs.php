<?php namespace ewma\controllers\main;

class Logs extends \Controller
{
    public function write($name)
    {
        $d = $this->d(':' . $name, [
            'enabled' => true
        ]);

        if ($d['enabled']) {
            $this->writeLine($name);
        }
    }

    private function writeLine($name)
    {
        $file = fopen(abs_path('logs/' . $name . '.log'), 'a+');

        fwrite($file, $this->c('>' . $name . ':render', $this->data));

        fclose($file);
    }
}
