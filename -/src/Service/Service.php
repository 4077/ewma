<?php namespace ewma\Service;

class Service
{
    protected $proxy;

    protected $services = [];

    public $__branch__ = [];

    public function __register__()
    {
        if (null !== $this->proxy) {
            $this->proxy = new $this->proxy;
        }

        foreach ($this->services as $serviceName) {
            $serviceClassName = $this->{$serviceName};

            if (is_string($serviceClassName)) {
                if (isset($this->__branch__[$serviceClassName])) {
                    $this->{$serviceName} = $this->__branch__[$serviceClassName];
                } else {
                    $this->{$serviceName} = new Provider($this, $serviceName, $serviceClassName);
                }
            }
        }

        $this->boot();
    }

    protected function boot()
    {

    }

    /**
     * Запуск сервиса
     */
    public function up()
    {
        return $this;
    }

    public function __call($method, $parameters)
    {
        if (null !== $this->proxy) {
            return call_user_func_array([$this->proxy, $method], $parameters);
        }

        if (method_exists($this, $method)) {
            call_user_func_array([$this, $method], $parameters);
        } else {
            throw new \Exception('Service ' . get_class() . ' does not have method ' . $method);
        }
    }

    public function __get($property)
    {
        if (null !== $this->proxy) {
            return $this->proxy->{$property};
        } else {
            return $this->{$property};
        }
    }

    public function __set($property, $value)
    {
        if (null !== $this->proxy) {
            $this->proxy->{$property} = $value;
        } else {
            $this->{$property} = $value;
        }
    }
}
