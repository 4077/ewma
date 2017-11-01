<?php namespace ewma\handlers\ui\assignments\controllers\main;

use ewma\handlers\Assignments as AssignmentsSvc;
use ewma\handlers\models\Assignment as AssignmentModel;

class Value extends \Controller
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

//        $globalsEditable = $this->contextData['globals_editable'];

        $valueData = _j($this->assignment->data);

        $editable = true;//!$this->assignment->is_global || $globalsEditable;

        $requestsData = [
            'context'       => $this->context,
            'assignment_id' => $this->assignment->id
        ];

        $v->assign([
                       'HANDLER_ID'     => $this->assignment->id,
                       'DISABLED_CLASS' => $this->assignment->enabled ? '' : 'disabled',
                       'NAME_TXT'       => $this->c('\std\ui txt:view', [
                           'path'              => 'input:valueNameUpdate',
                           'editable'          => $editable,
                           'data'              => $requestsData,
                           'class'             => 'txt',
                           'title'             => 'Название',
                           'placeholder'       => '...',
                           'fitInputToClosest' => '.bar',
                           'content'           => $valueData['name']
                       ]),
                       'VALUE'          => $this->valueView($valueData, $editable),
                       'CP'             => $this->c('~value/cp:view', [
                           'assignment' => $this->assignment
                       ], 'context')
                   ]);

        if ($this->assignment->source) {
            $v->assign('has_reference', [
                'REFERENCE' => $this->assignment->source_used ? 'G' : 'L'
            ]);
        }

//        $valueSelector = $this->_selector("|" . $this->assignment->id) . " > .bar";
//        $this->c('\std\ui button:bind', [
//            'selector' => $valueSelector,
//            'path'     => 'input:showValueCp',
//            'data'     => $requestsData
//        ]);

        $this->css()->import('common');
        $this->css('>cp')->import('common');

        return $v;
    }

    private function valueView($assignmentData, $editable)
    {
        $updateRequestData = [
            'context'       => $this->context,
            'assignment_id' => $this->assignment->id
        ];

        if ($assignmentData['type'] == 'bool') {
            return $this->c('\std\ui button:view', [
                'clickable' => $editable,
                'path'      => 'input:invertBoolValue',
                'data'      => $updateRequestData,
                'class'     => 'bool ' . ($assignmentData['value']['bool'] ? 'true' : 'false'),
                'title'     => 'Двоичное значение',
                'content'   => $assignmentData['value']['bool'] ? 'true' : 'false'
            ]);
        }

        if ($assignmentData['type'] == 'string') {
            return $this->c('\std\ui txt:view', [
                'editable'          => $editable,
                'path'              => 'input:updateStringValue',
                'title'             => 'Строковое значение',
                'data'              => $updateRequestData,
                'class'             => 'txt string',
                'fitInputToClosest' => '.bar',
                'content'           => $assignmentData['value']['string']
            ]);
        }

        if ($assignmentData['type'] == 'array') {
            return $this->c('\std\ui\data~:view|' . $this->_nodeId() . '/' . $this->assignment->id, [
                'read_call'  => [':readArrayTypeValue', $updateRequestData],
                'write_call' => [':writeArrayTypeValue', $updateRequestData]
            ]);
        }

        if ($assignmentData['type'] == 'expression') {
            return $this->c('\std\ui txt:view', [
                'editable'          => $editable,
                'path'              => 'input:updateExpressionValue',
                'data'              => $updateRequestData,
                'class'             => 'txt expression',
                'title'             => 'Выражение',
                'fitInputToClosest' => '.bar',
                'content'           => $assignmentData['value']['expression']
            ]);
        }
    }

    public function readArrayTypeValue()
    {
        $assignmentData = _j($this->assignment->data);

        return $assignmentData['value'][$assignmentData['type']];
    }

    public function writeArrayTypeValue()
    {
        AssignmentsSvc::updateData($this->assignment->id, 'value/array', $this->data['data']);
    }
}
