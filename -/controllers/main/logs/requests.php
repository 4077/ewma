<?php namespace ewma\controllers\main\logs;

class Requests extends \Controller
{
    public function render()
    {
        if ($this->data['type'] == 'xhr') {
            $line = 'XHR: ' . $this->data['call'][0] . ' ' . $this->plainData($this->data['call'][1]);
        }

        if ($this->data['type'] == 'route') {
            $line = 'ROUTE: ' . $this->data['route'];
        }

        if (empty($line)) {
            $line = 'unknown';
        }

        $user = $this->_user('login') or
        $user = '-';

        return $_SERVER['REMOTE_ADDR'] . ' [' . dt(time()) . '] ' . $user . ' ' . $line . "\n";
    }

    private function plainData($input)
    {
        $output = [];

        $list = a2f($input);

        foreach ($list as $path => $value) {
            if (empty($value)) {
                $value = false;
            }

            $output[] = $path . '=' . str_replace(' ', '\\ ', $value);
        }

        return implode(' ', $output);
    }
}
