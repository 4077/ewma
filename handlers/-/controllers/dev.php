<?php namespace ewma\handlers\controllers;

class Dev extends \Controller
{
    public function fix1()
    {
//        $varAssignments = \ewma\handlers\models\Assignment::where('type', 'VAR')->get();
//
//        foreach ($varAssignments as $varAssignment) {
//            $data = _j($varAssignment->data);
//
//            $data['path'] = $data['name'];
//            $data['name'] = '';
//
//            $varAssignment->data = j_($data);
//            $varAssignment->save();
//        }
    }

    public function fix2()
    {
//        $valueAssignments = \ewma\handlers\models\Assignment::where('type', 'VALUE')->get();
//
//        foreach ($valueAssignments as $valueAssignment) {
//            $data = _j($valueAssignment->data);
//
//            unset($data['expression']);
//            $data['value']['expression'] = '';
//
//            $valueAssignment->data = j_($data);
//            $valueAssignment->save();
//        }
    }

    public function fix3()
    {
//        $valueAssignments = \ewma\handlers\models\Assignment::where('type', 'INPUT')->orWhere('type', 'VAR')->orWhere('type', 'OUTPUT')->get();
//
//        foreach ($valueAssignments as $valueAssignment) {
//            $data = _j($valueAssignment->data);
//
//            $data['combine_mode'] = strtolower($data['combine_mode']);
//
//            $valueAssignment->data = j_($data);
//            $valueAssignment->save();
//        }
    }

    public function fix4()
    {
        $valueAssignments = \ewma\handlers\models\Assignment::where('type', 'VALUE')->get();

        foreach ($valueAssignments as $valueAssignment) {
            $data = _j($valueAssignment->data);

            $data['name'] = '';

            $valueAssignment->data = j_($data);
            $valueAssignment->save();
        }
    }
}
