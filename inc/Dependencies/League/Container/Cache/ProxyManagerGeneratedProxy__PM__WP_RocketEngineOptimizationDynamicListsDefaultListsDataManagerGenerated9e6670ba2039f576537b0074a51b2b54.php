<?php

namespace ProxyManagerGeneratedProxy\__PM__\WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists\DataManager;

class Generated9e6670ba2039f576537b0074a51b2b54 extends \WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists\DataManager implements \ProxyManager\Proxy\VirtualProxyInterface
{
    /**
     * @var \WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists\DataManager|null wrapped object, if the proxy is initialized
     */
    private $valueHolder5c912 = null;

    /**
     * @var \Closure|null initializer responsible for generating the wrapped object
     */
    private $initializerb4055 = null;

    /**
     * @var bool[] map of public properties of the parent class
     */
    private static $publicProperties3f2b9 = [
        
    ];

    private static $signature9e6670ba2039f576537b0074a51b2b54 = 'YTo0OntzOjk6ImNsYXNzTmFtZSI7czo2NzoiV1BfUm9ja2V0XEVuZ2luZVxPcHRpbWl6YXRpb25cRHluYW1pY0xpc3RzXERlZmF1bHRMaXN0c1xEYXRhTWFuYWdlciI7czo3OiJmYWN0b3J5IjtzOjUwOiJQcm94eU1hbmFnZXJcRmFjdG9yeVxMYXp5TG9hZGluZ1ZhbHVlSG9sZGVyRmFjdG9yeSI7czoxOToicHJveHlNYW5hZ2VyVmVyc2lvbiI7czo0ODoidjEuMC4xNkBlY2FkYmRjOTA1MmU0YWQwOGM2MGM4YTAyMjY4NzEyZTUwNDI3ZjdjIjtzOjEyOiJwcm94eU9wdGlvbnMiO2E6MDp7fX0=';

    public function get_lists()
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'get_lists', array(), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->get_lists();
    }

    public function get_lists_hash()
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'get_lists_hash', array(), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->get_lists_hash();
    }

    public function save_dynamic_lists(string $content)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'save_dynamic_lists', array('content' => $content), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->save_dynamic_lists($content);
    }

    /**
     * Constructor for lazy initialization
     *
     * @param \Closure|null $initializer
     */
    public static function staticProxyConstructor($initializer)
    {
        static $reflection;

        $reflection = $reflection ?? new \ReflectionClass(__CLASS__);
        $instance   = $reflection->newInstanceWithoutConstructor();

        unset($instance->cache_duration);

        \Closure::bind(function (\WP_Rocket\Engine\Optimization\DynamicLists\AbstractDataManager $instance) {
            unset($instance->filesystem);
        }, $instance, 'WP_Rocket\\Engine\\Optimization\\DynamicLists\\AbstractDataManager')->__invoke($instance);

        $instance->initializerb4055 = $initializer;

        return $instance;
    }

    public function __construct($filesystem = null)
    {
        static $reflection;

        if (! $this->valueHolder5c912) {
            $reflection = $reflection ?? new \ReflectionClass('WP_Rocket\\Engine\\Optimization\\DynamicLists\\DefaultLists\\DataManager');
            $this->valueHolder5c912 = $reflection->newInstanceWithoutConstructor();
        unset($this->cache_duration);

        \Closure::bind(function (\WP_Rocket\Engine\Optimization\DynamicLists\AbstractDataManager $instance) {
            unset($instance->filesystem);
        }, $this, 'WP_Rocket\\Engine\\Optimization\\DynamicLists\\AbstractDataManager')->__invoke($this);

        }

        $this->valueHolder5c912->__construct($filesystem);
    }

    public function & __get($name)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, '__get', ['name' => $name], $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        if (isset(self::$publicProperties3f2b9[$name])) {
            return $this->valueHolder5c912->$name;
        }

        $realInstanceReflection = new \ReflectionClass('WP_Rocket\\Engine\\Optimization\\DynamicLists\\DefaultLists\\DataManager');

        if (! $realInstanceReflection->hasProperty($name)) {
            $targetObject = $this->valueHolder5c912;

            $backtrace = debug_backtrace(false, 1);
            trigger_error(
                sprintf(
                    'Undefined property: %s::$%s in %s on line %s',
                    $realInstanceReflection->getName(),
                    $name,
                    $backtrace[0]['file'],
                    $backtrace[0]['line']
                ),
                \E_USER_NOTICE
            );
            return $targetObject->$name;
        }

        $targetObject = $this->valueHolder5c912;
        $accessor = function & () use ($targetObject, $name) {
            return $targetObject->$name;
        };
        $backtrace = debug_backtrace(true, 2);
        $scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
        $accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));
        $returnValue = & $accessor();

        return $returnValue;
    }

    public function __set($name, $value)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, '__set', array('name' => $name, 'value' => $value), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        $realInstanceReflection = new \ReflectionClass('WP_Rocket\\Engine\\Optimization\\DynamicLists\\DefaultLists\\DataManager');

        if (! $realInstanceReflection->hasProperty($name)) {
            $targetObject = $this->valueHolder5c912;

            $targetObject->$name = $value;

            return $targetObject->$name;
        }

        $targetObject = $this->valueHolder5c912;
        $accessor = function & () use ($targetObject, $name, $value) {
            $targetObject->$name = $value;

            return $targetObject->$name;
        };
        $backtrace = debug_backtrace(true, 2);
        $scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
        $accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));
        $returnValue = & $accessor();

        return $returnValue;
    }

    public function __isset($name)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, '__isset', array('name' => $name), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        $realInstanceReflection = new \ReflectionClass('WP_Rocket\\Engine\\Optimization\\DynamicLists\\DefaultLists\\DataManager');

        if (! $realInstanceReflection->hasProperty($name)) {
            $targetObject = $this->valueHolder5c912;

            return isset($targetObject->$name);
        }

        $targetObject = $this->valueHolder5c912;
        $accessor = function () use ($targetObject, $name) {
            return isset($targetObject->$name);
        };
        $backtrace = debug_backtrace(true, 2);
        $scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
        $accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));
        $returnValue = $accessor();

        return $returnValue;
    }

    public function __unset($name)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, '__unset', array('name' => $name), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        $realInstanceReflection = new \ReflectionClass('WP_Rocket\\Engine\\Optimization\\DynamicLists\\DefaultLists\\DataManager');

        if (! $realInstanceReflection->hasProperty($name)) {
            $targetObject = $this->valueHolder5c912;

            unset($targetObject->$name);

            return;
        }

        $targetObject = $this->valueHolder5c912;
        $accessor = function () use ($targetObject, $name) {
            unset($targetObject->$name);

            return;
        };
        $backtrace = debug_backtrace(true, 2);
        $scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
        $accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));
        $accessor();
    }

    public function __clone()
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, '__clone', array(), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        $this->valueHolder5c912 = clone $this->valueHolder5c912;
    }

    public function __sleep()
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, '__sleep', array(), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return array('valueHolder5c912');
    }

    public function __wakeup()
    {
        unset($this->cache_duration);

        \Closure::bind(function (\WP_Rocket\Engine\Optimization\DynamicLists\AbstractDataManager $instance) {
            unset($instance->filesystem);
        }, $this, 'WP_Rocket\\Engine\\Optimization\\DynamicLists\\AbstractDataManager')->__invoke($this);
    }

    public function setProxyInitializer(\Closure $initializer = null) : void
    {
        $this->initializerb4055 = $initializer;
    }

    public function getProxyInitializer() : ?\Closure
    {
        return $this->initializerb4055;
    }

    public function initializeProxy() : bool
    {
        return $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'initializeProxy', array(), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;
    }

    public function isProxyInitialized() : bool
    {
        return null !== $this->valueHolder5c912;
    }

    public function getWrappedValueHolderValue()
    {
        return $this->valueHolder5c912;
    }
}
