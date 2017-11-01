<?php namespace ewma\logs\controllers;

class Main extends \Controller
{
    public function write($name)
    {
        $d = $this->d(':' . $name, [
            'enabled' => true
        ]);

        if ($d['enabled']) {
            if ($name == 'requests') {
                $this->requests();
            }
        }
    }

    private function requests()
    {
        $file = fopen(abs_path('logs/requests.log'), 'a+');

        if ($this->data['type'] == 'xhr') {
            $line = 'XHR: ' . $this->data['call'][0] . ' ' . $this->plainData($this->data['call'][1]);
        }

        if ($this->data['type'] == 'route') {
            $line = 'ROUTE: ' . $this->data['route'];
        }

        empty($line) && $line = 'unknown';

        $user = $this->_user('login') or
        $user = '-';

        fwrite($file, $_SERVER['REMOTE_ADDR'] . ' [' . dt(time()) . '] ' . $user . ' ' . $line . "\n");
        fclose($file);
    }

    private function plainData($input)
    {
        $output = [];

        $list = a2f($input);

        foreach ($list as $path => $value) {
            if (empty($value)) { // превращение пустого массива в false
                $value = false;
            }
            $output[] = $path . '=' . str_replace(' ', '\\ ', $value);
        }

        return implode(' ', $output);
    }
}
