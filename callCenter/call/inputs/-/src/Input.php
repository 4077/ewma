<?php namespace ewma\callCenter\call\inputs;

class Input
{
    public $number;

    public $call;

    public $name;

    public $path;

    public $type;

    public $null;

    public $value;

    public function __construct($call, $number, $data)
    {
        $this->number = $number;
        $this->call = $call;
        $this->name = $data['name'];
        $this->path = $data['path'];
        $this->type = $data['type'];
        $this->null = $data['null'];
        $this->value = new InputValue($data['value']);
    }

    public function pack()
    {
        $pack = $this->call->id . ':' . $this->number;

        return $pack;
    }

    public static function unpack($pack)
    {
        list($callId, $inputNumber) = explode(':', $pack);

        if ($call = \ewma\callCenter\models\Call::find($callId)) {
            $inputs = _j($call->inputs);

            if (isset($inputs[$inputNumber])) {
                return new self($call, $inputNumber, $inputs[$inputNumber]);
            }
        }
    }

    public function fresh()
    {
        $this->call = $this->call->fresh();

        $inputs = _j($this->call->inputs);

        $inputData = $inputs[$this->number];

        $this->name = $inputData['name'];
        $this->path = $inputData['path'];
        $this->type = $inputData['type'];
        $this->null = $inputData['null'];
        $this->value = new InputValue($inputData['value']);
    }

    public function getData()
    {
        return [
            'name'  => $this->name,
            'path'  => $this->path,
            'type'  => $this->type,
            'null'  => $this->null,
            'value' => $this->value->getData()
        ];
    }

    public function save()
    {
        $inputs = _j($this->call->inputs);

        $inputs[$this->number] = $this->getData();

        $this->call->inputs = j_($inputs);
        $this->call->save();
    }
}
