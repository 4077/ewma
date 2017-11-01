<?php namespace ewma\callCenter\controllers\main;

class Output extends \Controller
{
    public function reload()
    {
        $this->jquery()->replace($this->view());
    }

    public function view()
    {
        $v = $this->v();

        $s = $this->s('~');

        if (isset($s['output_call_id_by_cat_id'][$s['selected_cat_id']])) {
            $callId = $s['output_call_id_by_cat_id'][$s['selected_cat_id']];

            if ($call = \ewma\callCenter\models\Call::find($callId)) {
                list($value, $class) = $this->outputView($call);

                $v->assign([
                               'CLASS' => $class,
                               'VALUE' => $value
                           ]);
            }
        }

        $this->css();

        return $v;
    }

    public function outputView($call)
    {
        $output = _j($call->last_output);

        $callPack = pack_model($call);

        if (is_null($output)) {
            $class = 'null';
            $value = 'null';
        } elseif (is_numeric($output)) {
            $class = 'number';
            $value = $output;
        } elseif (is_bool($output)) {
            $class = 'bool ' . ($output ? 'true' : 'false');
            $value = $output;
        } elseif (is_string($output)) {
            $class = 'string';
            $value = $output;
        } elseif (is_array($output)) {
            $class = 'data';
            $value = $this->c('\std\ui\data~:view|' . $this->_nodeInstance(), [
                'read_call'  => $this->_abs('>app:readOutputData', ['call' => $callPack]),
                'write_call' => $this->_abs('>app:writeOutputData', ['call' => $callPack])
            ]);
        } else {
            $value = 'undefined';
            $class = 'undefined';
        }

        return [$value, $class];
    }
}
