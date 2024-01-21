<?php

namespace ProxyManagerGeneratedProxy\__PM__\WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration;

class Generatede3c0c0eeb1ea1b259589ad4694797aad extends \WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration implements \ProxyManager\Proxy\VirtualProxyInterface
{
    /**
     * @var \WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration|null wrapped object, if the proxy is initialized
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

    private static $signaturee3c0c0eeb1ea1b259589ad4694797aad = 'YTo0OntzOjk6ImNsYXNzTmFtZSI7czo1MToiV1BfUm9ja2V0XEVuZ2luZVxDcml0aWNhbFBhdGhcQ3JpdGljYWxDU1NHZW5lcmF0aW9uIjtzOjc6ImZhY3RvcnkiO3M6NTA6IlByb3h5TWFuYWdlclxGYWN0b3J5XExhenlMb2FkaW5nVmFsdWVIb2xkZXJGYWN0b3J5IjtzOjE5OiJwcm94eU1hbmFnZXJWZXJzaW9uIjtzOjQ4OiJ2MS4wLjE2QGVjYWRiZGM5MDUyZTRhZDA4YzYwYzhhMDIyNjg3MTJlNTA0MjdmN2MiO3M6MTI6InByb3h5T3B0aW9ucyI7YTowOnt9fQ==';

    public function dispatch()
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'dispatch', array(), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->dispatch();
    }

    public function push_to_queue($data)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'push_to_queue', array('data' => $data), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->push_to_queue($data);
    }

    public function save()
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'save', array(), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->save();
    }

    public function update($key, $data)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'update', array('key' => $key, 'data' => $data), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->update($key, $data);
    }

    public function delete($key)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'delete', array('key' => $key), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->delete($key);
    }

    public function maybe_handle()
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'maybe_handle', array(), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->maybe_handle();
    }

    public function schedule_cron_healthcheck($schedules)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'schedule_cron_healthcheck', array('schedules' => $schedules), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->schedule_cron_healthcheck($schedules);
    }

    public function handle_cron_healthcheck()
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'handle_cron_healthcheck', array(), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->handle_cron_healthcheck();
    }

    public function cancel_process()
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'cancel_process', array(), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->cancel_process();
    }

    public function data($data)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'data', array('data' => $data), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->data($data);
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

        unset($instance->prefix, $instance->action, $instance->processor, $instance->start_time, $instance->cron_hook_identifier, $instance->cron_interval_identifier, $instance->identifier, $instance->data);

        $instance->initializerb4055 = $initializer;

        return $instance;
    }

    public function __construct(\WP_Rocket\Engine\CriticalPath\ProcessorService $processor)
    {
        static $reflection;

        if (! $this->valueHolder5c912) {
            $reflection = $reflection ?? new \ReflectionClass('WP_Rocket\\Engine\\CriticalPath\\CriticalCSSGeneration');
            $this->valueHolder5c912 = $reflection->newInstanceWithoutConstructor();
        unset($this->prefix, $this->action, $this->processor, $this->start_time, $this->cron_hook_identifier, $this->cron_interval_identifier, $this->identifier, $this->data);

        }

        $this->valueHolder5c912->__construct($processor);
    }

    public function & __get($name)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, '__get', ['name' => $name], $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        if (isset(self::$publicProperties3f2b9[$name])) {
            return $this->valueHolder5c912->$name;
        }

        $realInstanceReflection = new \ReflectionClass('WP_Rocket\\Engine\\CriticalPath\\CriticalCSSGeneration');

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

        $realInstanceReflection = new \ReflectionClass('WP_Rocket\\Engine\\CriticalPath\\CriticalCSSGeneration');

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

        $realInstanceReflection = new \ReflectionClass('WP_Rocket\\Engine\\CriticalPath\\CriticalCSSGeneration');

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

        $realInstanceReflection = new \ReflectionClass('WP_Rocket\\Engine\\CriticalPath\\CriticalCSSGeneration');

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
        unset($this->prefix, $this->action, $this->processor, $this->start_time, $this->cron_hook_identifier, $this->cron_interval_identifier, $this->identifier, $this->data);
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
