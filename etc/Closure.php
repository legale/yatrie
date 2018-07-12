<?php

class Noreflect
{
    private $class;

    public function __construct(&$class)
    {
        $this->class = $class;
    }

    public function __get($key)
    {

    }

    public function __set($key, $value)
    {

    }


    public function &call(string $method, array &$args)
    {
        return Closure::bind(function (string $method, array &$args) {
            return call_user_func_array($this->{$name}, $args);
        }, $this->class, get_class($this->class));
    }

}
