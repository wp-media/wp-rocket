<?php

namespace ProxyManagerGeneratedProxy\__PM__\WP_Rocket\Engine\Optimization\GoogleFonts\CombineV2;

class Generated72017dc193efa1b143377ef23c65e08d extends \WP_Rocket\Engine\Optimization\GoogleFonts\CombineV2 implements \ProxyManager\Proxy\VirtualProxyInterface
{
    /**
     * @var \WP_Rocket\Engine\Optimization\GoogleFonts\CombineV2|null wrapped object, if the proxy is initialized
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

    private static $signature72017dc193efa1b143377ef23c65e08d = 'YTo0OntzOjk6ImNsYXNzTmFtZSI7czo1MToiV1BfUm9ja2V0XEVuZ2luZVxPcHRpbWl6YXRpb25cR29vZ2xlRm9udHNcQ29tYmluZVYyIjtzOjc6ImZhY3RvcnkiO3M6NTA6IlByb3h5TWFuYWdlclxGYWN0b3J5XExhenlMb2FkaW5nVmFsdWVIb2xkZXJGYWN0b3J5IjtzOjE5OiJwcm94eU1hbmFnZXJWZXJzaW9uIjtzOjQ4OiJ2MS4wLjE2QGVjYWRiZGM5MDUyZTRhZDA4YzYwYzhhMDIyNjg3MTJlNTA0MjdmN2MiO3M6MTI6InByb3h5T3B0aW9ucyI7YTowOnt9fQ==';

    public function optimize($html) : string
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'optimize', array('html' => $html), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->optimize($html);
    }

    public function has_google_fonts()
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'has_google_fonts', array(), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->has_google_fonts();
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

        unset($instance->display_values, $instance->has_google_fonts);

        \Closure::bind(function (\WP_Rocket\Engine\Optimization\GoogleFonts\CombineV2 $instance) {
            unset($instance->xmp_replace, $instance->html_replace, $instance->svg_replace);
        }, $instance, 'WP_Rocket\\Engine\\Optimization\\GoogleFonts\\CombineV2')->__invoke($instance);

        $instance->initializerb4055 = $initializer;

        return $instance;
    }

    public function __construct()
    {
        static $reflection;

        if (! $this->valueHolder5c912) {
            $reflection = $reflection ?? new \ReflectionClass('WP_Rocket\\Engine\\Optimization\\GoogleFonts\\CombineV2');
            $this->valueHolder5c912 = $reflection->newInstanceWithoutConstructor();
        unset($this->display_values, $this->has_google_fonts);

        \Closure::bind(function (\WP_Rocket\Engine\Optimization\GoogleFonts\CombineV2 $instance) {
            unset($instance->xmp_replace, $instance->html_replace, $instance->svg_replace);
        }, $this, 'WP_Rocket\\Engine\\Optimization\\GoogleFonts\\CombineV2')->__invoke($this);

        }
    }

    public function & __get($name)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, '__get', ['name' => $name], $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        if (isset(self::$publicProperties3f2b9[$name])) {
            return $this->valueHolder5c912->$name;
        }

        $realInstanceReflection = new \ReflectionClass('WP_Rocket\\Engine\\Optimization\\GoogleFonts\\CombineV2');

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

        $realInstanceReflection = new \ReflectionClass('WP_Rocket\\Engine\\Optimization\\GoogleFonts\\CombineV2');

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

        $realInstanceReflection = new \ReflectionClass('WP_Rocket\\Engine\\Optimization\\GoogleFonts\\CombineV2');

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

        $realInstanceReflection = new \ReflectionClass('WP_Rocket\\Engine\\Optimization\\GoogleFonts\\CombineV2');

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
        unset($this->display_values, $this->has_google_fonts);

        \Closure::bind(function (\WP_Rocket\Engine\Optimization\GoogleFonts\CombineV2 $instance) {
            unset($instance->xmp_replace, $instance->html_replace, $instance->svg_replace);
        }, $this, 'WP_Rocket\\Engine\\Optimization\\GoogleFonts\\CombineV2')->__invoke($this);
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
