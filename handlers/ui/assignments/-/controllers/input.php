<?php namespace ewma\handlers\ui\assignments\controllers;

use ewma\handlers\models\Assignment as AssignmentModel;
use ewma\handlers\models\Cat as CatModel;
use ewma\handlers\Assignments;

class Input extends \Controller
{
    public $allow = [self::XHR, self::APP];

    // CONTAINER (OUTPUT, INPUT, VAR)

//    public function showContainerCp()
//    {
//        if ($assignment = Assignments::getContainer($this->data('assignment_id'))) {
//            $this->jquery(".container[container_id='" . $this->data['assignment_id'] . "'] > .bar > .cp")
//                ->width($this->jquery(".container[container_id='" . $this->data['assignment_id'] . "'] > .bar")->outerWidth())
//                ->html($this->c('~container/cp:view', [
//                    'assignment' => $assignment
//                ], 'context'));
//        }
//    }

    public function outputNameUpdate()
    {
        if ($this->dataHas('value')) {
            $txt = \std\ui\Txt::value($this);

            if ($output = Assignments::getOutput($this->data('assignment_id'))) {
                $value = $txt->value;

                if (!is_numeric($value)) {
                    $output->name = $txt->value;
                    $output->save();

                    $txt->response();
                } else {
                    $txt->response($output->name);
                }
            }
        }
    }

    public function setContainerCombineMode($mode)
    {
        if ($assignment = Assignments::setContainerCombineMode($this->data('assignment_id'), $mode)) {
            $this->reloadFor($assignment);
            $this->setObsolete();
        }
    }

    public function containerNameUpdate()
    {
        if ($this->dataHas('value')) {
            $txt = \std\ui\Txt::value($this);

            Assignments::updateData($this->data('assignment_id'), 'name', $txt->value);

            $txt->response();
        }
    }

    public function containerPathUpdate()
    {
        if ($this->dataHas('value')) {
            $txt = \std\ui\Txt::value($this);

            Assignments::updateData($this->data('assignment_id'), 'path', $txt->value);

            $txt->response();
            $this->setObsolete();
        }
    }

    public function containerEnabledToggle()
    {
        if ($assignment = Assignments::toggleEnabled($this->data('assignment_id'))) {
            $this->reloadFor($assignment);
            $this->setObsolete();
        }
    }

    public function containerRequiredToggle()
    {
        if ($assignment = Assignments::toggleRequired($this->data('assignment_id'))) {
            $this->reloadFor($assignment);
            $this->setObsolete();
        }
    }

    public function containerCacheEnabledToggle()
    {
        if ($assignment = Assignments::toggleCacheEnabled($this->data('assignment_id'))) {
            $this->reloadFor($assignment);
            $this->setObsolete();
        }
    }

    public function createVar()
    {
        if ($var = Assignments::createVar($this->data('assignment_id'))) {
            $this->reloadFor($var);
        }
    }

    public function createInput()
    {
        if ($input = Assignments::createInput($this->data('assignment_id'))) {
            $this->reloadFor($input);
        }
    }

    public function addGlobalInput()
    {

    }

    // HANDLER

//    public function showHandlerCp()
//    {
//        if ($assignment = Assignments::getHandler($this->data('assignment_id'))) {
//            $this->jquery($this->_selector("~handler:|" . $this->data['assignment_id']) . " > .bar > .cp")
//                ->width($this->jquery($this->_selector("~handler:|" . $this->data['assignment_id']) . " > .bar")->width())
//                ->html($this->c('~handler/cp:view', [
//                    'assignment' => $assignment
//                ], 'context'));
//        }
//    }

    public function handlerNameUpdate()
    {
        if ($this->dataHas('value')) {
            $txt = \std\ui\Txt::value($this);

            Assignments::updateData($this->data('assignment_id'), 'name', $txt->value);

            $txt->response();
        }
    }

    public function handlerPathUpdate()
    {
        if ($this->dataHas('value')) {
            $txt = \std\ui\Txt::value($this);

            Assignments::updateData($this->data('assignment_id'), 'path', $txt->value);

            $txt->response();
            $this->setObsolete();
        }
    }

    public function handlerEnabledToggle()
    {
        if ($assignment = Assignments::toggleEnabled($this->data('assignment_id'))) {
            $this->reloadFor($assignment);
            $this->setObsolete();
        }
    }

    public function handlerRequiredToggle()
    {
        if ($assignment = Assignments::toggleRequired($this->data('assignment_id'))) {
            $this->reloadFor($assignment);
            $this->setObsolete();
        }
    }

    public function handlerCacheEnabledToggle()
    {
        if ($assignment = Assignments::toggleCacheEnabled($this->data('assignment_id'))) {
            $this->reloadFor($assignment);
            $this->setObsolete();
        }
    }

    public function createHandler()
    {
        if ($handler = Assignments::createHandler($this->data('assignment_id'))) {
            $this->reloadFor($handler);
        }
    }

    public function addGlobalHandler()
    {

    }

    // VALUE

//    public function showValueCp()
//    {
//        if ($assignment = Assignments::getValue($this->data('assignment_id'))) {
//            $this->jquery($this->_selector("~value:|" . $this->data['assignment_id']) . " > .bar > .cp_container > .cp")
//                ->width($this->jquery($this->_selector("~value:|" . $this->data['assignment_id']) . " > .bar > .cp_container")->width())
//                ->html($this->c('~value/cp:view', [
//                    'assignment' => $assignment
//                ], 'context'));
//        }
//    }

    public function valueNameUpdate()
    {
        if ($this->dataHas('value')) {
            $txt = \std\ui\Txt::value($this);

            Assignments::updateData($this->data('assignment_id'), 'name', $txt->value);

            $txt->response();
        }
    }

    public function createValue()
    {
        if ($value = Assignments::createValue($this->data('assignment_id'))) {
            $this->reloadFor($value->parent);
        }
    }

    public function setValueType($type = false)
    {
        if ($assignment = Assignments::setValueType($this->data('assignment_id'), $type)) {
            $this->reloadFor($assignment);
            $this->setObsolete();
        }
    }

    public function updateStringValue()
    {
        if ($this->dataHas('value')) {
            $txt = \std\ui\Txt::value($this);

            if (Assignments::updateData($this->data('assignment_id'), 'value/string', $txt->value)) {
                $txt->response();
                $this->setObsolete();
            }
        }
    }

    public function invertBoolValue()
    {
        if (null !== $value = Assignments::getData($this->data('assignment_id'), 'value/bool')) {
            Assignments::updateData($this->data('assignment_id'), 'value/bool', !$value);
            $this->reloadFor(Assignments::getValue($this->data('assignment_id')));
            $this->setObsolete();
        }
    }

    public function updateExpressionValue()
    {
        if ($this->dataHas('value')) {
            $txt = \std\ui\Txt::value($this);

            if (Assignments::updateData($this->data('assignment_id'), 'value/expression', $txt->value)) {
                $txt->response();
                $this->setObsolete();
            }
        }
    }

    public function valueEnabledToggle()
    {
        if ($assignment = Assignments::toggleEnabled($this->data('assignment_id'))) {
            $this->reloadFor($assignment->parent);
            $this->setObsolete();
        }
    }

    // common

    public function reorder()
    {
        if ($this->dataHas('sequence array')) {
            foreach ($this->data['sequence'] as $n => $assignmentId) {
                if (is_numeric($n) && $assignment = AssignmentModel::find($assignmentId)) {
                    $assignment->update(['position' => ($n + 1) * 10]);
                }
            }

            $this->setObsolete();
        }
    }

    public function exchange()
    {
        if ($assignment = AssignmentModel::find($this->data('assignment_id'))) {
            $this->c('\std\ui\dialogs~:open:exchange|ewma/handlers', [
                'default'             => [
                    'pluginOptions/width' => 500
                ],
                'path'                => '\std\data\exchange~:view|ewma/handlers',
                'data'                => [
                    'target_name' => '#' . $assignment->id,
                    'import_call' => $this->_abs('^app/exchange:import', ['assignment' => pack_model($assignment)]),
                    'export_call' => $this->_abs('^app/exchange:export', ['assignment' => pack_model($assignment)])
                ],
                'pluginOptions/title' => 'handlers'
            ]);
        }
    }

    public function copy()
    {
        if ($assignment = AssignmentModel::find($this->data('assignment_id'))) {
            $copiedAssignmentId = &$this->s('~:copied_assignment/id');
            $copiedAssignmentId = $assignment->id;

            $copiedAssignmentType = &$this->s('~:copied_assignment/type');
            $copiedAssignmentType = $assignment->type;

            $output = $assignment;
            while ($parent = $output->parent) {
                $output = $parent;
            }

            $this->c('~output:reload', [
                'context'    => $this->data('context'),
                'assignment' => $output
            ]);

            $this->widget('~output:|' . $output->id, 'bind');

//            $this->jsCall('ewma.trigger', '');
        }
    }

    public function paste()
    {
        $copiedAssignmentId = $this->s('~:copied_assignment/id');

        if ($targetAssignment = Assignments::paste($copiedAssignmentId, $this->data('assignment_id'))) {
            $this->reloadFor($targetAssignment);
            $this->setObsolete();
        }
    }

    public function saveAsGlobal()
    {
        if ($assignment = Assignments::get($this->data('assignment_id'))) {
            if ($cat = CatModel::find($this->data('cat_id'))) {
                Assignments::saveAsGlobal($this->data('assignment_id'), $cat->id);

                $this->c('\std\ui\dialogs~:close:cat_selector|ewma/handlers');
            } else {
                $this->c('\std\ui\dialogs~:open:cat_selector|ewma/handlers', [
                    'path' => '@catSelector~:view',
                    'data' => [
                        'context'      => 'handlers/saveAsGlobal',
                        'context_data' => [
                            'callbacks' => [
                                'select' => $this->_abs([
                                                            ':saveAsGlobal',
                                                            [
                                                                'assignment_id' => $this->data['assignment_id']
                                                            ]
                                                        ])
                            ]
                        ]
                    ]
                ]);
            }

            $this->reloadFor($assignment);
            $this->setObsolete();
        }
    }

    public function useGlobal()
    {
        if ($assignment = Assignments::useGlobal($this->data('assignment_id'))) {
            $this->reloadFor($assignment);
            $this->setObsolete();
        }
    }

    public function useLocal()
    {
        if ($assignment = Assignments::useLocal($this->data('assignment_id'))) {
            $this->reloadFor($assignment);
            $this->setObsolete();
        }
    }

    public function delete()
    {
        $assignment = AssignmentModel::find($this->data('assignment_id'));

        if ($assignment and $deleted = Assignments::delete($this->data('assignment_id'))) {
            $copiedAssignmentId = &$this->s('~:copied_assignment/id');
            if (in($copiedAssignmentId, $deleted)) {
                $copiedAssignmentId = false;
            }

            $this->reloadFor($assignment);
            $this->setObsolete();
        }
    }

    public function addGlobalDialog()
    {
        $this->c('\std\ui\dialogs~:open:global_selector|ewma/handlers', [
            'path' => '@globalSelector~',
            'data' => [
                'context'      => 'handlers/addGlobal',
                'context_data' => [
                    'callbacks' => [
                        'select' => $this->_abs([
                                                    ':addGlobal',
                                                    [
                                                        'assignment_id' => $this->data['assignment_id']
                                                    ]
                                                ])
                    ]
                ]
            ]
        ]);
    }

    public function addGlobal()
    {
        if ($item = \ewma\handlers\models\CatItem::find($this->data('item_id'))) {
            if ($assignment = $item->assignment) {
                Assignments::saveAsLocal($this->data('assignment_id'), $assignment->id);
            }
        }
    }

    private function reloadFor($assignment)
    {
        $target = $assignment;

//        if (in($assignment->type, 'INPUT, VAR')) {
//            $reloadPath = '~handler:reload';
//            $target = $assignment->parent;
//        } elseif ($assignment->type == 'HANDLER') {
//            $reloadPath = '~handler:reload';
//        } elseif ($assignment->type == 'VALUE') {
//            $reloadPath = '~value:reload';
//        } elseif ($assignment->type == 'OUTPUT') {
//            $reloadPath = '~output:reload';
//        }

        if (in($assignment->type, 'INPUT, VAR')) {
            $reloadPath = '~handler:reload';
            $target = $assignment->parent;
        } elseif ($assignment->type == 'HANDLER') {
            $target = $assignment->parent;

            if (in($target->type, 'INPUT, VAR')) {
                $reloadPath = '~handler:reload';
                $target = $target->parent;
            } else { // OUTPUT
                $reloadPath = '~output:reload';
            }
        } elseif ($assignment->type == 'VALUE') {
            $target = $assignment->parent->parent or
            $target = $assignment->parent;

            if (in($target->type, 'HANDLER')) {
                $reloadPath = '~handler:reload';
            } else { // OUTPUT
                $reloadPath = '~output:reload';
            }
        } elseif ($assignment->type == 'OUTPUT') {
            $reloadPath = '~output:reload';
        }

        if (isset($reloadPath)) {
            $this->c($reloadPath, [
                'context'    => $this->data('context'),
                'assignment' => $target
            ]);
        }

        $output = $target;
        while ($parent = $output->parent) {
            $output = $parent;
        }

        $this->widget('~output:|' . $output->id, 'bind');

        // todo if $target->getRoot() is global then trigger self reload for this root locals
    }

    private function setObsolete()
    {
        $this->performCallback('set_obsolete');
    }

    private function performCallback($event)
    {
        if ($context = $this->data('context')) {
            if ($callback = $this->d('contexts:callbacks/' . $event . '|' . $context)) {
                $this->_call($callback)->perform();

                return true;
            }
        }
    }
}
