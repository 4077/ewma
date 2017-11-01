<?php namespace ewma\callCenter\call\inputs\controllers;

class Main extends \Controller
{
    private $call;

    public function __create()
    {
        $this->call = $this->unpackModel('call');

        $this->instance_($this->call->id);
    }

    public function reload()
    {
        $this->jquery('|')->replace($this->view());
    }

    public function view()
    {
        $v = $this->v('|');

        $call = $this->call;
        $callXPack = xpack_model($call);

        $inputs = _j($this->call->inputs) or
        $inputs = [];

        foreach ($inputs as $number => $inputData) {
            $input = new \ewma\callCenter\call\inputs\Input($call, $number, $inputData);

            $inputPack = $input->pack();

            $v->assign('input', [
                'NUMBER'           => $number,
                'NAME'             => $this->c('\std\ui txt:view', [
                    'path'              => '>xhr:updateName',
                    'data'              => [
                        'input' => $inputPack
                    ],
                    'class'             => 'txt',
                    'fitInputToClosest' => '.name',
                    'content'           => $input->name
                ]),
                'PATH'             => $this->c('\std\ui txt:view', [
                    'path'              => '>xhr:updatePath',
                    'data'              => [
                        'input' => $inputPack
                    ],
                    'class'             => 'txt',
                    'fitInputToClosest' => '.path',
                    'content'           => $input->path
                ]),
                'TYPE_SELECT'      => $this->c('\std\ui select:view', [
                    'path'     => '>xhr:setType',
                    'data'     => [
                        'input' => $inputPack
                    ],
                    'items'    => ['string', 'bool', 'data'],
                    'combine'  => true,
                    'selected' => $input->type
                ]),
                'DUPLICATE_BUTTON' => $this->c('\std\ui button:view', [
                    'path'    => '>xhr:duplicate',
                    'data'    => [
                        'input' => $inputPack
                    ],
                    'class'   => 'duplicate button',
                    'title'   => 'Дублировать',
                    'content' => '<div class="icon"></div>'
                ]),
                'DELETE_BUTTON'    => $this->c('\std\ui button:view', [
                    'path'    => '>xhr:delete',
                    'data'    => [
                        'input' => $inputPack
                    ],
                    'class'   => 'delete button',
                    'title'   => 'Удалить',
                    'content' => '<div class="icon"></div>'
                ]),
                'VALUE'            => $this->valueView($input),
                'TYPE_CLASS'       => $input->type,
                'NULL_BUTTON'      => $this->c('\std\ui button:view', [
                    'path'    => '>xhr:toggleNull',
                    'data'    => [
                        'input' => $inputPack
                    ],
                    'class'   => 'null_button ' . ($input->null ? 'pressed' : ''),
                    'content' => 'null'
                ])
            ]);
        }

        $this->c('\std\ui sortable:bind', [
            'selector'       => $this->_selector('|') . ' .inputs',
            'items_id_attr'  => 'input_number',
            'path'           => '>xhr:rearrange',
            'data'           => [
                'call' => $callXPack
            ],
            'plugin_options' => [
                'distance' => 20,
                'axis'     => 'y'
            ]
        ]);

        $v->assign([
                       'CREATE_BUTTON' => $this->c('\std\ui button:view', [
                           'path'    => '>xhr:create',
                           'data'    => [
                               'call' => $callXPack
                           ],
                           'class'   => 'create_button',
                           'content' => 'создать вход'
                       ])
                   ]);

        $this->css(':\css\std~, \jquery\ui icons');

        $this->e('ewma/callCenter/calls/update/inputs')->rebind(':reload');

        return $v;
    }

    private function valueView(\ewma\callCenter\call\inputs\Input $input)
    {
        $inputPack = $input->pack();

        if ($input->type == 'string') {
            return $this->c('\std\ui txt:view', [
                'path'              => '>xhr:updateValue',
                'data'              => [
                    'input' => $inputPack,
                    'type'  => 'string'
                ],
                'class'             => 'txt',
                'fitInputToClosest' => '.value',
                'content'           => $input->value->string
            ]);
        }

        if ($input->type == 'bool') {
            return $this->c('\std\ui button:view', [
                'path'    => '>xhr:updateValue',
                'data'    => [
                    'input' => $inputPack,
                    'type'  => 'bool'
                ],
                'class'   => 'bool_value_button ' . ($input->value->bool ? 'true' : 'false'),
                'content' => $input->value->bool ? 'true' : 'false',
            ]);
        }

        if ($input->type == 'data') {
            return $this->c('\std\ui\data~:view|' . $this->_nodeInstance(), [
                'read_call'  => $this->_abs('>app:readData', ['input' => $inputPack]),
                'write_call' => $this->_abs('>app:writeData', ['input' => $inputPack])
            ]);
        }
    }
}
