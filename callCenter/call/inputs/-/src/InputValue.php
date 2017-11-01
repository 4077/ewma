<?php namespace ewma\callCenter\call\inputs;

class InputValue
{
    public $string;

    public $bool;

    public $data;

    public function __construct($data)
    {
        $this->string = $data['string'];
        $this->bool = $data['bool'];
        $this->data = $data['data'];
    }

    public function getData()
    {
        return [
            'string' => $this->string,
            'bool'   => $this->bool,
            'data'   => $this->data
        ];
    }
}
