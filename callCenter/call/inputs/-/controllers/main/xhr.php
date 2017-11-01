<?php namespace ewma\callCenter\call\inputs\controllers\main;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    public function __create()
    {
        $this->a() or $this->lock();
    }

    private $call;

    private function getInputs($call = null)
    {
        $this->call = $call or
        $this->call = $this->unxpackModel('call');

        if ($this->call) {
            $inputs = _j($this->call->inputs) or
            $inputs = [];

            return $inputs;
        }
    }

    private function saveInputs($inputs)
    {
        $this->call->inputs = j_($inputs);
        $this->call->save();
    }

    private function getInput()
    {
        return \ewma\callCenter\call\inputs\Input::unpack($this->data('input'));
    }

    public function create()
    {
        if (null !== $inputs = $this->getInputs()) {
            $inputs[] = $this->getInputDefault();

            $this->saveInputs($inputs);

            $this->e('ewma/callCenter/calls/update/inputs', ['call_id' => $this->call->id])->trigger(['call' => $this->call]);
        }
    }

    public function delete()
    {
        if ($this->data('discarded')) {
            $this->c('\std\ui\dialogs~:close:deleteInputConfirm|ewma/callCenter');
        } else {
            if ($input = $this->getInput()) {
                $call = $input->call;

                if ($this->data('confirmed')) {
                    $inputs = $this->getInputs($input->call);
                    $inputs = array_values(map($inputs, diff(array_keys($inputs), $input->number, true)));
                    $this->saveInputs($inputs);

                    $this->c('\std\ui\dialogs~:close:deleteInputConfirm|ewma/callCenter');

                    $this->e('ewma/callCenter/calls/update/inputs', ['call_id' => $call->id])->trigger(['call' => $call]);
                } else {
                    $inputName = $input->name or
                    $inputName = $input->path or
                    $inputName = $input->number;

                    $callName = $call->name or
                    $callName = $call->path or
                    $callName = '...';

                    $this->c('\std\ui\dialogs~:open:deleteInputConfirm|ewma/callCenter', [
                        'path'          => '\std dialogs/confirm~:view',
                        'data'          => [
                            'confirm_call' => $this->_abs([':delete', $this->data]),
                            'discard_call' => $this->_abs([':delete', $this->data]),
                            'message'      => 'Удалить вход <b>' . $inputName . '</b> вызова <b>' . $callName . '</b>?'
                        ],
                        'pluginOptions' => [
                            'resizable' => 'false'
                        ]
                    ]);
                }
            }
        }
    }

    public function rearrange()
    {
        if ($inputs = $this->getInputs()) {
            $sequence = $this->data('sequence');

            $inputs = array_values(map($inputs, $sequence));

            $this->saveInputs($inputs);

            $this->e('ewma/callCenter/calls/update/inputs', ['call_id' => $this->call->id])->trigger(['call' => $this->call]);
        }
    }

    public function duplicate()
    {
        if ($input = $this->getInput()) {
            $inputs = $this->getInputs($input->call);

            insert($inputs, $input->number, $input->getData());

            $this->saveInputs($inputs);

            $this->e('ewma/callCenter/calls/update/inputs', ['call_id' => $this->call->id])->trigger(['call' => $this->call]);
        }
    }

    public function updateName()
    {
        if ($input = $this->getInput()) {
            $txt = \std\ui\Txt::value($this);

            $input->name = $txt->value;
            $input->save();

            $txt->response();
        }
    }

    public function updatePath()
    {
        if ($input = $this->getInput()) {
            $txt = \std\ui\Txt::value($this);

            $input->path = $txt->value;
            $input->save();

            $txt->response();
        }
    }

    public function setType()
    {
        if ($input = $this->getInput()) {
            if (in($this->data('value'), 'string, bool, data')) {
                $input->type = $this->data['value'];
                $input->save();

                $this->e('ewma/callCenter/calls/update/inputs', ['call_id' => $input->call->id])->trigger(['call' => $input->call]);
            }
        }
    }

    public function updateValue()
    {
        if ($input = $this->getInput()) {
            $type = $this->data('type');

            if ($type == 'string') {
                $txt = \std\ui\Txt::value($this);

                $input->value->string = $txt->value;
                $input->save();

                $txt->response();
            }

            if ($type == 'bool') {
                invert($input->value->bool);

                $input->save();

                $this->e('ewma/callCenter/calls/update/inputs', ['call_id' => $input->call->id])->trigger(['call' => $input->call]);
            }
        }
    }

    public function toggleNull()
    {
        if ($input = $this->getInput()) {
            invert($input->null);

            $input->save();

            $this->e('ewma/callCenter/calls/update/inputs', ['call_id' => $input->call->id])->trigger(['call' => $input->call]);
        }
    }

    private function getInputDefault()
    {
        return [
            'name'  => '',
            'path'  => '',
            'type'  => 'string',
            'null'  => false,
            'value' => [
                'bool'   => false,
                'string' => '',
                'data'   => []
            ]
        ];
    }
}
