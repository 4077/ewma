<?php namespace ewma\handlers\ui\assignments\controllers;

use ewma\handlers\models\Assignment as AssignmentModel;

class Main extends \Controller
{
    public function view()
    {
        return $this->c_('>assignments:view');
    }

    public function assignmentView()
    {
        if ($this->dataHas('assignment')) {
            $assignment = $this->data['assignment'];
        }

        if ($this->dataHas('assignment_id')) {
            $assignment = AssignmentModel::find($this->data['assignment_id']);
        }

        if (!empty($assignment)) {
            $viewData = [
                'assignment'   => $assignment,
                'context'      => $this->data['context'],
                'context_data' => $this->data['context_data'],
            ];

            if ($assignment->type == 'HANDLER') {
                return $this->c('>handler:view', $viewData);
            }

            if ($assignment->type == 'VALUE') {
                return $this->c('>value:view', $viewData);
            }

            if ($assignment->type == 'OUTPUT') {
                return $this->c('>output:view', $viewData);
            }
        }
    }

    public function outputView()
    {
        $v = $this->v();

        $v->assign([
                       'CONTENT' => $this->c_('>output:view')
                   ]);

        $this->c('\std\ui\dialogs~:addContainer:ewma/handlers');

        return $v;
    }
}
