<?php namespace ewma\callCenter\call\controllers\main;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    public function __create()
    {
        $this->a() or $this->lock();
    }

    public function perform()
    {
        if ($this->data('discarded')) {
            $this->closePerformDialogs();
        } else {
            if ($call = $this->unxpackModel('call')) {
                $hasInputs = _j($call->inputs);
                $confirmationRequired = $call->require_confirmation;

                if ($hasInputs) {
                    if ($this->data('inputs_form_confirmed')) {
                        if ($confirmationRequired) {
                            if ($this->data('confirmed')) {
                                $this->performCall($call);
                            } else {
                                $this->inputsFormDialogClose();
                                $this->confirmDialogOpen($call);
                            }
                        } else {
                            $this->performCall($call);
                        }
                    } else {
                        $this->inputsFormDialogOpen($call);
                    }
                } else {
                    if ($confirmationRequired) {
                        if ($this->data('confirmed')) {
                            $this->performCall($call);
                        } else {
                            $this->confirmDialogOpen($call);
                        }
                    } else {
                        $this->performCall($call);
                    }
                }
            }
        }
    }

    private function performCall($call)
    {
        $callData = _j($call->data);

        $s = $this->s('~inputsForm|');

        if (!empty($s['inputs'])) {
            ra($callData, $s['inputs']);

            foreach (a2f($s['nulls']) as $path => $value) {
                if ($value) {
                    $callDataValue = &ap($callData, $path);
                    $callDataValue = null;
                }
            }
        }

        $output = $this->_call([$call->path, $callData])->perform();

        $call->last_output = j_($output);
        $call->save();

        $this->closePerformDialogs();

        $this->showOutput();
    }

    private function getCallName($call)
    {
        $callName = $call->name or
        $callName = $call->path or
        $callName = '...';

        return $callName;
    }

    private function inputsFormDialogOpen($call)
    {
        $this->c('\std\ui\dialogs~:open:inputsForm|ewma/callCenter', [
            'path'          => '~inputsForm:view',
            'data'          => [
                'call'         => $this->data['call'],
                'confirm_call' => $this->_abs([':perform|', $this->data]),
                'discard_call' => $this->_abs([':perform|', $this->data])
            ],
            'pluginOptions' => [
                'title'     => 'Данные для вызова ' . $this->getCallName($call),
                'resizable' => 'false'
            ]
        ]);
    }

    private function confirmDialogOpen($call)
    {
        $this->c('\std\ui\dialogs~:open:performConfirm|ewma/callCenter', [
            'path'          => '\std dialogs/confirm~:view',
            'data'          => [
                'confirm_call' => $this->_abs([':perform|', $this->data]),
                'discard_call' => $this->_abs([':perform|', $this->data]),
                'message'      => 'Выполнить вызов <b>' . $this->getCallName($call) . '</b>?'
            ],
            'pluginOptions' => [
                'resizable' => 'false'
            ]
        ]);
    }

    private function inputsFormDialogClose()
    {
        $this->c('\std\ui\dialogs~:close:inputsForm|ewma/callCenter');
    }

    private function closePerformDialogs()
    {
        $this->c('\std\ui\dialogs~:close:inputsForm|ewma/callCenter');
        $this->c('\std\ui\dialogs~:close:performConfirm|ewma/callCenter');
    }

    public function toggleSettingsVisible()
    {
        if ($call = $this->unxpackModel('call')) {
            $s = &$this->s('~|');

            invert($s['settings_visible']);

            $this->c('<:reload', [], 'call');
        }
    }

    public function duplicate()
    {
        if ($call = $this->unxpackModel('call')) {
            \ewma\callCenter\models\Call::create($call->toArray());

            $this->e('ewma/callCenter/calls/create', ['cat_id' => $call->cat->id])->trigger(['call' => $call]);
        }
    }

    public function rename()
    {
        if ($call = $this->unxpackModel('call')) {
            $txt = \std\ui\Txt::value($this);

            $call->name = $txt->value;
            $call->save();

            $txt->response(
                $call->name ? $call->name : ($call->path ? $call->path : ''),
                $call->name
            );
        }
    }

    public function updatePath()
    {
        if ($call = $this->unxpackModel('call')) {
            $txt = \std\ui\Txt::value($this);

            $call->path = $txt->value;
            $call->save();

            $txt->response();
        }
    }

    public function toggleRequireConfirmation()
    {
        if ($call = $this->unxpackModel('call')) {
            $call->require_confirmation = !$call->require_confirmation;
            $call->save();

            $this->e('ewma/callCenter/calls/update/require_confirmation', ['call_id' => $call->id])->trigger(['call' => $call]);
        }
    }

    public function delete()
    {
        if ($this->data('discarded')) {
            $this->c('\std\ui\dialogs~:close:deleteCallConfirm|ewma/callCenter');
        } else {
            if ($call = $this->unxpackModel('call')) {
                if ($this->data('confirmed')) {
                    $call->delete();

                    $this->c('\std\ui\dialogs~:close:deleteCallConfirm|ewma/callCenter');

                    $this->e('ewma/callCenter/calls/delete', ['cat_id' => $call->cat->id])->trigger(['call' => $call]);
                } else {
                    $this->c('\std\ui\dialogs~:open:deleteCallConfirm|ewma/callCenter', [
                        'path'          => '\std dialogs/confirm~:view',
                        'data'          => [
                            'confirm_call' => $this->_abs([':delete', ['call' => $this->data['call']]]),
                            'discard_call' => $this->_abs([':delete', ['call' => $this->data['call']]]),
                            'message'      => 'Удалить вызов <b>' . ($call->name ? $call->name : ($call->path ? $call->path : '...')) . '</b>?'
                        ],
                        'pluginOptions' => [
                            'resizable' => 'false'
                        ]
                    ]);
                }
            }
        }
    }

    public function showOutput()
    {
        if ($call = $this->unxpackModel('call')) {
            $s = &$this->s('^');

            $s['output_call_id_by_cat_id'][$s['selected_cat_id']] = $call->id;

            $this->c('^~output:reload');

            $this->jquery(".show_output_button")->removeClass('pressed');
            $this->jquery(".show_output_button[call_id='" . $call->id . "']")->addClass('pressed');
        }
    }
}
