<?php

namespace ProxyManagerGeneratedProxy\__PM__\WP_Rocket\Engine\CriticalPath\CriticalCSS;

class Generatedba65bc1d1b27d5b773082e3b4b449845 extends \WP_Rocket\Engine\CriticalPath\CriticalCSS implements \ProxyManager\Proxy\VirtualProxyInterface
{
    /**
     * @var \WP_Rocket\Engine\CriticalPath\CriticalCSS|null wrapped object, if the proxy is initialized
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
        'process' => true,
        'items' => true,
    ];

    private static $signatureba65bc1d1b27d5b773082e3b4b449845 = 'YTo0OntzOjk6ImNsYXNzTmFtZSI7czo0MToiV1BfUm9ja2V0XEVuZ2luZVxDcml0aWNhbFBhdGhcQ3JpdGljYWxDU1MiO3M6NzoiZmFjdG9yeSI7czo1MDoiUHJveHlNYW5hZ2VyXEZhY3RvcnlcTGF6eUxvYWRpbmdWYWx1ZUhvbGRlckZhY3RvcnkiO3M6MTk6InByb3h5TWFuYWdlclZlcnNpb24iO3M6NDg6InYxLjAuMTZAZWNhZGJkYzkwNTJlNGFkMDhjNjBjOGEwMjI2ODcxMmU1MDQyN2Y3YyI7czoxMjoicHJveHlPcHRpb25zIjthOjA6e319';

    public function get_critical_css_path()
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'get_critical_css_path', array(), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->get_critical_css_path();
    }

    public function process_handler($version = 'default', $clean_version = '')
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'process_handler', array('version' => $version, 'clean_version' => $clean_version), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->process_handler($version, $clean_version);
    }

    public function stop_generation()
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'stop_generation', array(), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->stop_generation();
    }

    public function clean_critical_css($version = 'default')
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'clean_critical_css', array('version' => $version), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->clean_critical_css($version);
    }

    public function get_critical_css_content()
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'get_critical_css_content', array(), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->get_critical_css_content();
    }

    public function get_current_page_critical_css()
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'get_current_page_critical_css', array(), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->get_current_page_critical_css();
    }

    public function is_async_css_mobile()
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'is_async_css_mobile', array(), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->is_async_css_mobile();
    }

    public function get_exclude_async_css()
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'get_exclude_async_css', array(), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->get_exclude_async_css();
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

        unset($instance->process, $instance->items);

        \Closure::bind(function (\WP_Rocket\Engine\CriticalPath\CriticalCSS $instance) {
            unset($instance->options, $instance->critical_css_path, $instance->filesystem);
        }, $instance, 'WP_Rocket\\Engine\\CriticalPath\\CriticalCSS')->__invoke($instance);

        $instance->initializerb4055 = $initializer;

        return $instance;
    }

    public function __construct(\WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration $process, \WP_Rocket\Admin\Options_Data $options, $filesystem)
    {
        static $reflection;

        if (! $this->valueHolder5c912) {
            $reflection = $reflection ?? new \ReflectionClass('WP_Rocket\\Engine\\CriticalPath\\CriticalCSS');
            $this->valueHolder5c912 = $reflection->newInstanceWithoutConstructor();
        unset($this->process, $this->items);

        \Closure::bind(function (\WP_Rocket\Engine\CriticalPath\CriticalCSS $instance) {
            unset($instance->options, $instance->critical_css_path, $instance->filesystem);
        }, $this, 'WP_Rocket\\Engine\\CriticalPath\\CriticalCSS')->__invoke($this);

        }

        $this->valueHolder5c912->__construct($process, $options, $filesystem);
    }

    public function & __get($name)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, '__get', ['name' => $name], $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        if (isset(self::$publicProperties3f2b9[$name])) {
            return $this->valueHolder5c912->$name;
        }

        $realInstanceReflection = new \ReflectionClass('WP_Rocket\\Engine\\CriticalPath\\CriticalCSS');

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

        if (isset(self::$publicProperties3f2b9[$name])) {
            return ($this->valueHolder5c912->$name = $value);
        }

        $realInstanceReflection = new \ReflectionClass('WP_Rocket\\Engine\\CriticalPath\\CriticalCSS');

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

        if (isset(self::$publicProperties3f2b9[$name])) {
            return isset($this->valueHolder5c912->$name);
        }

        $realInstanceReflection = new \ReflectionClass('WP_Rocket\\Engine\\CriticalPath\\CriticalCSS');

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

        if (isset(self::$publicProperties3f2b9[$name])) {
            unset($this->valueHolder5c912->$name);

            return;
        }

        $realInstanceReflection = new \ReflectionClass('WP_Rocket\\Engine\\CriticalPath\\CriticalCSS');

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
        unset($this->process, $this->items);

        \Closure::bind(function (\WP_Rocket\Engine\CriticalPath\CriticalCSS $instance) {
            unset($instance->options, $instance->critical_css_path, $instance->filesystem);
        }, $this, 'WP_Rocket\\Engine\\CriticalPath\\CriticalCSS')->__invoke($this);
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
