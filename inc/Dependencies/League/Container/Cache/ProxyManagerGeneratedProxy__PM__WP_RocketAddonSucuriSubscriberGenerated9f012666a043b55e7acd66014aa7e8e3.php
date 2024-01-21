<?php

namespace ProxyManagerGeneratedProxy\__PM__\WP_Rocket\Addon\Sucuri\Subscriber;

class Generated9f012666a043b55e7acd66014aa7e8e3 extends \WP_Rocket\Addon\Sucuri\Subscriber implements \ProxyManager\Proxy\VirtualProxyInterface
{
    /**
     * @var \WP_Rocket\Addon\Sucuri\Subscriber|null wrapped object, if the proxy is initialized
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

    private static $signature9f012666a043b55e7acd66014aa7e8e3 = 'YTo0OntzOjk6ImNsYXNzTmFtZSI7czozMzoiV1BfUm9ja2V0XEFkZG9uXFN1Y3VyaVxTdWJzY3JpYmVyIjtzOjc6ImZhY3RvcnkiO3M6NTA6IlByb3h5TWFuYWdlclxGYWN0b3J5XExhenlMb2FkaW5nVmFsdWVIb2xkZXJGYWN0b3J5IjtzOjE5OiJwcm94eU1hbmFnZXJWZXJzaW9uIjtzOjQ4OiJ2MS4wLjE2QGVjYWRiZGM5MDUyZTRhZDA4YzYwYzhhMDIyNjg3MTJlNTA0MjdmN2MiO3M6MTI6InByb3h5T3B0aW9ucyI7YTowOnt9fQ==';

    public function maybe_clean_firewall_cache()
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'maybe_clean_firewall_cache', array(), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->maybe_clean_firewall_cache();
    }

    public function do_admin_post_rocket_purge_sucuri()
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'do_admin_post_rocket_purge_sucuri', array(), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->do_admin_post_rocket_purge_sucuri();
    }

    public function maybe_print_notice()
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'maybe_print_notice', array(), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->maybe_print_notice();
    }

    public function add_cdn_helper_message(array $addons) : array
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'add_cdn_helper_message', array('addons' => $addons), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->add_cdn_helper_message($addons);
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

        \Closure::bind(function (\WP_Rocket\Addon\Sucuri\Subscriber $instance) {
            unset($instance->options);
        }, $instance, 'WP_Rocket\\Addon\\Sucuri\\Subscriber')->__invoke($instance);

        $instance->initializerb4055 = $initializer;

        return $instance;
    }

    public function __construct(\WP_Rocket\Admin\Options_Data $options)
    {
        static $reflection;

        if (! $this->valueHolder5c912) {
            $reflection = $reflection ?? new \ReflectionClass('WP_Rocket\\Addon\\Sucuri\\Subscriber');
            $this->valueHolder5c912 = $reflection->newInstanceWithoutConstructor();
        \Closure::bind(function (\WP_Rocket\Addon\Sucuri\Subscriber $instance) {
            unset($instance->options);
        }, $this, 'WP_Rocket\\Addon\\Sucuri\\Subscriber')->__invoke($this);

        }

        $this->valueHolder5c912->__construct($options);
    }

    public function & __get($name)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, '__get', ['name' => $name], $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        if (isset(self::$publicProperties3f2b9[$name])) {
            return $this->valueHolder5c912->$name;
        }

        $realInstanceReflection = new \ReflectionClass('WP_Rocket\\Addon\\Sucuri\\Subscriber');

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

        $realInstanceReflection = new \ReflectionClass('WP_Rocket\\Addon\\Sucuri\\Subscriber');

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

        $realInstanceReflection = new \ReflectionClass('WP_Rocket\\Addon\\Sucuri\\Subscriber');

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

        $realInstanceReflection = new \ReflectionClass('WP_Rocket\\Addon\\Sucuri\\Subscriber');

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
        \Closure::bind(function (\WP_Rocket\Addon\Sucuri\Subscriber $instance) {
            unset($instance->options);
        }, $this, 'WP_Rocket\\Addon\\Sucuri\\Subscriber')->__invoke($this);
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
