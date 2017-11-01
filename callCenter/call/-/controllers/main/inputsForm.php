<?php namespace ewma\callCenter\call\controllers\main;

class InputsForm extends \Controller
{
    private $call;

    public function __create()
    {
        $this->call = $this->unxpackModel('call');

        $this->instance_($this->call->id);
    }

    public function reload()
    {
        $this->jquery('|')->replace($this->view());
    }

    public function view()
    {
        $v = $this->v('|');

        $s = &$this->s('|', [
            'inputs' => [],
            'nulls'  => []
        ]);

        $call = $this->call;

        foreach ((array)_j($call->inputs) as $number => $inputData) {
            $input = new \ewma\callCenter\call\inputs\Input($call, $number, $inputData);

            $label = $input->name or
            $label = $input->path or
            $label = '/';

            aa($s['inputs'], [$input->path => $input->value->{$input->type}]);
            aa($s['nulls'], [$input->path => $input->null]);

            $null = ap($s['nulls'], $input->path);

            $v->assign('input', [
                'LABEL'       => $label,
                'TYPE_CLASS'  => $input->type,
                'NULL_BUTTON' => $this->c('\std\ui button:view', [
                    'path'    => '>xhr:toggleNull|',
                    'data'    => [
                        'path' => $input->path,
                        'call' => xpack_model($this->call)
                    ],
                    'class'   => 'null_button ' . ($null ? 'pressed' : ''),
                    'content' => 'null'
                ])
            ]);

            $v = $this->assignValue($v, $input);
        }

        $v->assign([
                       'RESET_BUTTON'   => $this->c('\std\ui button:view', [
                           'path'    => $this->_p('>xhr:reset|'),
                           'data'    => [
                               'call' => xpack_model($call)
                           ],
                           'class'   => 'reset_button',
                           'content' => 'Сбросить'
                       ]),
                       'CONFIRM_BUTTON' => $this->c('\std\ui button:view', [
                           'path'    => $this->_p('~xhr:perform|'),
                           'data'    => [
                               'call'                  => xpack_model($call),
                               'inputs_form_confirmed' => true
                           ],
                           'class'   => 'button ' . ($call->require_confirmation ? 'red' : 'blue'),
                           'content' => 'Выполнить'
                       ]),
                       'DISCARD_BUTTON' => $this->c('\std\ui button:view', [
                           'path'    => $this->_p('~xhr:perform|'),
                           'data'    => [
                               'call'      => xpack_model($call),
                               'discarded' => true
                           ],
                           'class'   => 'button',
                           'content' => 'Закрыть'
                       ]),
                   ]);

        $this->css(':\css\std~');

        return $v;
    }

    private function assignValue(\ewma\Views\View $v, \ewma\callCenter\call\inputs\Input $input)
    {
        $s = &$this->s('|');

        $value = ap($s['inputs'], $input->path);

        if ($input->type == 'string') {
            $v->assign('input/string', [
                'INPUT_NUMBER' => $input->number,
                'VALUE'        => htmlspecialchars($value)
            ]);

            $this->c('\std\ui liveinput:bind', [
                'selector' => $this->_selector(". input[input_number='" . $input->number . "']"),
                'path'     => '>xhr:updateValue|',
                'data'     => [
                    'path' => $input->path,
                    'type' => 'string'
                ]
            ]);
        }

        if ($input->type == 'bool') {
            $v->assign('input/bool', [
                'BUTTON' => $this->c('\std\ui button:view', [
                    'path'    => '>xhr:updateValue|',
                    'data'    => [
                        'path' => $input->path,
                        'type' => 'bool',
                        'call' => xpack_model($this->call)
                    ],
                    'class'   => 'bool_value_button ' . ($value ? 'true' : 'false'),
                    'content' => $value ? 'true' : 'false',
                ])
            ]);
        }

        if ($input->type == 'data') {
            $v->assign('input/data', [
                'CONTENT' => $this->c('\std\ui\data~:view|' . $this->_nodeInstance(), [
                    'read_call'  => $this->_abs('>app:readData|', ['path' => $input->path]),
                    'write_call' => $this->_abs('>app:writeData|', ['path' => $input->path])
                ])
            ]);
        }

        return $v;
    }
}
