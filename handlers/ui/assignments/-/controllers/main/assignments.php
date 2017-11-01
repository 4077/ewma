<?php namespace ewma\handlers\ui\assignments\controllers\main;

use std\router\models\Assignment as AssignmentModel;

class Assignments extends \Controller
{
    private $assignment;

    public function __create()
    {
        if ($this->dataHas('assignment')) {
            $this->assignment = $this->data['assignment'];
        }

        if ($this->dataHas('assignment_id')) {
            $this->assignment = AssignmentModel::find($this->data['assignment_id']);
        }

        if ($this->assignment) {
            $this->setContext();
        } else {
            $this->lock();
        }
    }

    private $context;
    private $contextData;

    private function setContext()
    {
        $this->context = $this->data('context');
        $this->contextData = &$this->d('contexts:|' . $this->context);

        if ($this->dataHas('context_data')) {
            $this->contextData = $this->data['context_data'];
        }
    }

    public function reload()
    {
        $this->jquery("|" . $this->assignment->id)->replace($this->view());
    }

    public function view()
    {
        $v = $this->v('|' . $this->assignment->id);

        $v->assign('ASSIGNMENT_ID', $this->assignment->id);

        $nested = $this->assignment->nested()->orderBy('position')->get();

        foreach ($nested as $assignment) {
            $v->assign('assignment', [
                'ID'      => $assignment->id,
                'CONTENT' => $this->assignmentContentView($assignment)
            ]);
        }

        if (!empty($this->contextData['editable'])) {
            if (count($nested) > 1) {
                $this->c('\std\ui sortable:bind', [
                    'selector'       => $this->_selector("|" . $this->assignment->id) . " > .assignments",
                    'path'           => 'input:reorder',
                    'items_id_attr'  => 'assignment_' . $this->assignment->id . '_assignment_id',
                    'data'           => [
                        'context'  => $this->context,
                        'input_id' => $this->assignment->id
                    ],
                    'plugin_options' => [
                        'axis'     => 'y',
                        'distance' => 15
                    ]
                ]);
            }
        }

        $this->css();

        return $v;
    }

    private function assignmentContentView($assignment)
    {
        $assignmentViewData = [
            'context'    => $this->context,
            'assignment' => $assignment
        ];

        if ($assignment->type == 'HANDLER') {
            return $this->c('@handler:view', $assignmentViewData);
        }

        if ($assignment->type == 'VALUE') {
            return $this->c('@value:view', $assignmentViewData);
        }
    }
}
