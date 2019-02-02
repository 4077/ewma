<?php namespace ewma\Service;

class Provider
{
    private $container;

    private $property;

    private $serviceClassName;

    public function __construct(Service $container, $property, $serviceClassName)
    {
        $this->container = $container;
        $this->property = $property;
        $this->serviceClassName = $serviceClassName;
    }

    public function __call($method, $parameters)
    {
        $this->container->{$this->property} = $this->getService();

        return call_user_func_array([$this->container->{$this->property}, $method], $parameters);
    }

    public function __get($property)
    {
        $this->container->{$this->property} = $this->getService();

        return $this->container->{$this->property}->{$property};
    }

    public function __set($property, $value)
    {
        $this->container->{$this->property} = $this->getService();

        if ($this->container->{$this->property}->{$property} instanceof self) {
            $this->container->{$this->property}->{$property} = $value;
            $this->container->{$this->property}->register();
        } else {
            $this->container->{$this->property}->{$property} = $value;
        }
    }

    private function getService()
    {
        if (isset($this->container->__branch__[$this->serviceClassName])) {
            $service = $this->container->__branch__[$this->serviceClassName];
        } else {
            $service = new $this->serviceClassName;

            if ($service instanceof Service) {
                $service->__branch__ = $this->container->__branch__;
                $service->__branch__[get_class($this->container)] = $this->container;
                $service->__register__();
            }
        }

        return $service;
    }
}
