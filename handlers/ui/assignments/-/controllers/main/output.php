<?php namespace ewma\handlers\ui\assignments\controllers\main;

use ewma\handlers\Assignments;

class Output extends \Controller
{
    private $assignment;

    public function __create()
    {
        if ($this->dataHas('assignment') && $this->data['assignment']->type == 'OUTPUT') {
            $this->assignment = $this->data['assignment'];
        }

        if ($this->dataHas('output_id')) {
            $this->assignment = Assignments::getOutput($this->data['output_id']);
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

        $viewsData = [
            'context'    => $this->context,
            'assignment' => $this->assignment
        ];

        $v->assign([
                       'ASSIGNMENTS' => $this->c('@assignments:view', $viewsData),
                       'CP'          => $this->c('>cp:view', $viewsData)
                   ]);

        $this->css()->import('common');
        $this->css('>cp')->import('common');

        $this->widget(':|' . $this->assignment->id);

        return $v;
    }
}
