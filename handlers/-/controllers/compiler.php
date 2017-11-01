<?php namespace ewma\handlers\controllers;

use ewma\handlers\Assignments;

class Compiler extends \Controller
{
    public function compileOutput($assignmentId)
    {
        if ($assignment = Assignments::getOutput($assignmentId)) {
            $assignmentData = _j($assignment->data);

            return [
                'name'         => $assignment->name,
                'combine_mode' => $assignmentData['combine_mode'],
                'assignments'  => $this->compileAssignments($assignment)
            ];
        }
    }

    private function compileAssignments($assignment) // CONTAINER, INPUT, VAR
    {
        $output = [];

        $nested = $assignment->nested()->orderBy('position')->get();
        foreach ($nested as $nestedAssignment) {
            if ($nestedAssignment->enabled) {
                if ($nestedAssignment->type == 'HANDLER') {
                    $output[] = $this->compileHandler($nestedAssignment);
                }

                if ($nestedAssignment->type == 'VALUE') {
                    $output[] = $this->compileValue($nestedAssignment);
                }
            }
        }

        return $output;
    }

    private function compileHandler($assignment)
    {
        $assignments = [];

        $nested = $assignment->nested()->where('enabled', true)->orderBy('position')->get();
        foreach ($nested as $nestedAssignment) {
            if ($nestedAssignment->enabled) {
                if ($nestedAssignment->type == 'VAR') {
                    $assignments[] = $this->compileVar($nestedAssignment);
                }

                if ($nestedAssignment->type == 'INPUT') {
                    $assignments[] = $this->compileInput($nestedAssignment);
                }
            }
        }

        $assignmentData = _j($assignment->data);

        return [
            'type'        => 'HANDLER',
            'required'    => (int)$assignment->required,
            'path'        => $assignmentData['path'],
            'assignments' => $assignments
        ];
    }

    private function compileVar($assignment)
    {
        $assignmentData = _j($assignment->data);

        return [
            'type'         => 'VAR',
            'required'     => (int)$assignment->required,
            'path'         => $assignmentData['path'],
            'combine_mode' => $assignmentData['combine_mode'],
            'assignments'  => $this->compileAssignments($assignment)
        ];
    }

    private function compileInput($assignment)
    {
        $assignmentData = _j($assignment->data);

        return [
            'type'         => 'INPUT',
            'required'     => (int)$assignment->required,
            'path'         => $assignmentData['path'],
            'combine_mode' => $assignmentData['combine_mode'],
            'assignments'  => $this->compileAssignments($assignment)
        ];
    }

    private function compileValue($assignment)
    {
        $assignmentData = _j($assignment->data);

        return [
            'type'  => 'VALUE',
            'value' => $assignmentData['value'][$assignmentData['type']]
        ];
    }
}
