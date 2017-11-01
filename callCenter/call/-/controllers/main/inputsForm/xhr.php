<?php namespace ewma\callCenter\call\controllers\main\inputsForm;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    public function __create()
    {
        $this->a() or $this->lock();
    }

    public function updateValue()
    {
        $s = &$this->s('<|');

        $type = $this->data('type');

        if ($type == 'string') {
            ap($s['inputs'], $this->data('path'), $this->data('value'));
        }

        if ($type == 'bool') {
            $value = &ap($s['inputs'], $this->data('path'));

            invert($value);

            $this->c('<:reload', [], 'call');
        }
    }

    public function toggleNull()
    {
        $s = &$this->s('<|');

        $value = &ap($s['nulls'], $this->data('path'));

        invert($value);

        $this->c('<:reload', [], 'call');
    }

    public function reset()
    {
        if ($call = $this->unxpackModel('call')) {
            $s = &$this->s('<|');

            foreach ((array)_j($call->inputs) as $number => $inputData) {
                $input = new \ewma\callCenter\call\inputs\Input($call, $number, $inputData);

                ra($s['inputs'], [$input->path => $input->value->{$input->type}]);
                ra($s['nulls'], [$input->path => $input->null]);
            }

            $this->c('<:reload', [], 'call');
        }
    }
}
