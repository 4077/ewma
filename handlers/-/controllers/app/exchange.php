<?php namespace ewma\handlers\controllers\app;

class Exchange extends \Controller
{
    private $exportOutput = [];

    public function export()
    {
        $assignment = $this->unpackModel('assignment') or
        $assignment = \ewma\handlers\models\Assignment::find($this->data('assignment_id'));

        if ($assignment) {
            $tree = \ewma\Data\Tree::get(\ewma\handlers\models\Assignment::orderBy('position'));

            $this->exportOutput['assignment_id'] = $assignment->id;
            $this->exportOutput['assignment_type'] = $assignment->type;
            $this->exportOutput['assignments'] = $tree->getFlattenData($assignment->id);

            $this->exportRecursion($tree, $assignment);

            return $this->exportOutput;
        }
    }

    private function exportRecursion(\ewma\Data\Tree $tree, $assignment)
    {
//        $subAssignments = $tree->getSubnodes($assignment->id);
//        foreach ($subAssignments as $subAssignment) {
//            $this->exportRecursion($tree, $subAssignment);
//        }
    }

    public function import()
    {
        $targetAssignment = $this->unpackModel('assignment') or
        $targetAssignment = \ewma\handlers\models\Assignment::find($this->data('assignment_id'));

        $importData = $this->data('data');
        $sourceAssignmentId = $importData['assignment_id'];

        $this->importRecursion($targetAssignment, $importData, $sourceAssignmentId, $this->data('skip_first_level'));
    }

    private function importRecursion($targetAssignment, $importData, $assignmentId, $skipFirstLevel = false)
    {
        $newAssignmentData = $importData['assignments']['nodes_by_id'][$assignmentId];

        $newAssignment = false;

        if ($skipFirstLevel) {
            $newAssignment = $targetAssignment;
        } else {
            if (\ewma\handlers\Assignments::canBePasted($newAssignmentData['type'], $targetAssignment->type)) {
                $newAssignment = $targetAssignment->nested()->create($newAssignmentData);
            }
        }

        if ($newAssignment && !empty($importData['assignments']['ids_by_parent'][$assignmentId])) {
            foreach ($importData['assignments']['ids_by_parent'][$assignmentId] as $sourceAssignmentId) {
                $this->importRecursion($newAssignment, $importData, $sourceAssignmentId);
            }
        }
    }
}
