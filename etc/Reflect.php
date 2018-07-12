<?php

class Reflect
{
    /** @var string */
    private $class;
    /** @var object|null */
    private $object;

    public function __construct($classOrObject)
    {
        list($this->class, $this->object) = is_object($classOrObject)
            ? array(get_class($classOrObject), $classOrObject)
            : array((string)$classOrObject, null);
    }

    public function __get($key)
    {
        $property = new ReflectionProperty($this->class, $key);
        if (!$property->isPublic()) {
            $property->setAccessible(true);
        }

        return $property->isStatic()
            ? $property->getValue()
            : $property->getValue($this->object);
    }

    public function __set($key, $value)
    {
        $property = new ReflectionProperty($this->class, $key);
        if (!$property->isPublic()) {
            $property->setAccessible(true);
        }
        $property->isStatic()
            ? $property->setValue($value)
            : $property->setValue($this->object, $value);

        return $this;
    }

    public function call($methodName, array $args)
    {
        $method = new ReflectionMethod($this->class, $methodName);
        if (!$method->isPublic()) {
            $method->setAccessible(true);
        }

        return $method->isStatic()
            ? $method->invokeArgs(null, $args)
            : $method->invokeArgs($this->object, $args);
    }


    public function __call($methodName, array $args)
    {
        $method = new ReflectionMethod($this->class, $methodName);
        if (!$method->isPublic()) {
            $method->setAccessible(true);
        }
        array_walk($args, function ($el) {
            $el = &$el;
        }); //switch function arguments from value to reference

        return $method->isStatic()
            ? $method->invokeArgs(null, $args)
            : $method->invokeArgs($this->object, $args);
    }
}
