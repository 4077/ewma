<?php namespace ewma\Controllers;

class Call
{
    private $controller;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }

    private $path;

    private $data = [];

    public function set($data)
    {
        if ($data) {
            if (is_string($data)) {
                $this->path = $this->controller->_p($data);
            }

            if (is_array($data)) {
                $this->path = $this->controller->_p($data[0]);

                if (isset($data[1])) {
                    $this->data = $data[1];
                }
            }
        } else {
            $trace = debug_backtrace(false, 2);

            $this->path = $this->controller->_p(':' . $trace[1]['function']);
            $this->data = $this->controller->data;
        }

        return $this;
    }

    public function explode()
    {
        return [$this->path, $this->data];
    }

    public function explodeNamed()
    {
        return [
            'path' => $this->path,
            'data' => $this->data
        ];
    }

    /**
     * @param null $value
     *
     * @return mixed
     */
    public function path($value = null)
    {
        if (null === $value) {
            return $this->path;
        } else {
            $this->path = $this->controller->_p($value);

            return $this;
        }
    }

    public function data($path = false, $value = null)
    {
        if (null !== $value) {
            ap($this->data, $path, $value);

            return $this;
        } else {
            return ap($this->data, $path);
        }
    }

    public function aa($data)
    {
        aa($this->data, $data);

        return $this;
    }

    public function ra($data)
    {
        ra($this->data, $data);

        return $this;
    }

    public function perform($allowForCallPerform = Controller::APP)
    {
        $backup = $this->controller->__meta__->allowForCallPerform;

        $this->controller->__meta__->allowForCallPerform = $allowForCallPerform;

        $callResponse = $this->controller->c($this->path, $this->data);

        $this->controller->__meta__->allowForCallPerform = $backup;

        return $callResponse;
    }

    public function async($allowForCallPerform = Controller::APP)
    {
        $backup = $this->controller->__meta__->allowForCallPerform;

        $this->controller->__meta__->allowForCallPerform = $allowForCallPerform;

        $callResponse = $this->controller->async($this->path, $this->data);

        $this->controller->__meta__->allowForCallPerform = $backup;

        return $callResponse;
    }
}
