<?php

namespace ProxyManagerGeneratedProxy\__PM__\WP_Rocket\Engine\CriticalPath\Admin\Settings;

class Generated2a13cea90f116e78745d7a97f319a01b extends \WP_Rocket\Engine\CriticalPath\Admin\Settings implements \ProxyManager\Proxy\VirtualProxyInterface
{
    /**
     * @var \WP_Rocket\Engine\CriticalPath\Admin\Settings|null wrapped object, if the proxy is initialized
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

    private static $signature2a13cea90f116e78745d7a97f319a01b = 'YTo0OntzOjk6ImNsYXNzTmFtZSI7czo0NDoiV1BfUm9ja2V0XEVuZ2luZVxDcml0aWNhbFBhdGhcQWRtaW5cU2V0dGluZ3MiO3M6NzoiZmFjdG9yeSI7czo1MDoiUHJveHlNYW5hZ2VyXEZhY3RvcnlcTGF6eUxvYWRpbmdWYWx1ZUhvbGRlckZhY3RvcnkiO3M6MTk6InByb3h5TWFuYWdlclZlcnNpb24iO3M6NDg6InYxLjAuMTZAZWNhZGJkYzkwNTJlNGFkMDhjNjBjOGEwMjI2ODcxMmU1MDQyN2Y3YyI7czoxMjoicHJveHlPcHRpb25zIjthOjA6e319';

    public function display_cpcss_mobile_section()
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'display_cpcss_mobile_section', array(), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->display_cpcss_mobile_section();
    }

    public function enable_mobile_cpcss()
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'enable_mobile_cpcss', array(), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->enable_mobile_cpcss();
    }

    public function add_async_css_mobile_option($options)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'add_async_css_mobile_option', array('options' => $options), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->add_async_css_mobile_option($options);
    }

    public function set_async_css_mobile_default_value($new_version, $old_version)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'set_async_css_mobile_default_value', array('new_version' => $new_version, 'old_version' => $old_version), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->set_async_css_mobile_default_value($new_version, $old_version);
    }

    public function add_hidden_async_css_mobile($hidden_settings_fields)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'add_hidden_async_css_mobile', array('hidden_settings_fields' => $hidden_settings_fields), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->add_hidden_async_css_mobile($hidden_settings_fields);
    }

    public function generate($template, $data = [])
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'generate', array('template' => $template, 'data' => $data), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->generate($template, $data);
    }

    public function render_action_button($type, $action, $args = [])
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, 'render_action_button', array('type' => $type, 'action' => $action, 'args' => $args), $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        return $this->valueHolder5c912->render_action_button($type, $action, $args);
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

        \Closure::bind(function (\WP_Rocket\Engine\CriticalPath\Admin\Settings $instance) {
            unset($instance->beacon, $instance->options, $instance->critical_css);
        }, $instance, 'WP_Rocket\\Engine\\CriticalPath\\Admin\\Settings')->__invoke($instance);

        \Closure::bind(function (\WP_Rocket\Abstract_Render $instance) {
            unset($instance->template_path);
        }, $instance, 'WP_Rocket\\Abstract_Render')->__invoke($instance);

        $instance->initializerb4055 = $initializer;

        return $instance;
    }

    public function __construct(\WP_Rocket\Admin\Options_Data $options, \WP_Rocket\Engine\Admin\Beacon\Beacon $beacon, \WP_Rocket\Engine\CriticalPath\CriticalCSS $critical_css, $template_path)
    {
        static $reflection;

        if (! $this->valueHolder5c912) {
            $reflection = $reflection ?? new \ReflectionClass('WP_Rocket\\Engine\\CriticalPath\\Admin\\Settings');
            $this->valueHolder5c912 = $reflection->newInstanceWithoutConstructor();
        \Closure::bind(function (\WP_Rocket\Engine\CriticalPath\Admin\Settings $instance) {
            unset($instance->beacon, $instance->options, $instance->critical_css);
        }, $this, 'WP_Rocket\\Engine\\CriticalPath\\Admin\\Settings')->__invoke($this);

        \Closure::bind(function (\WP_Rocket\Abstract_Render $instance) {
            unset($instance->template_path);
        }, $this, 'WP_Rocket\\Abstract_Render')->__invoke($this);

        }

        $this->valueHolder5c912->__construct($options, $beacon, $critical_css, $template_path);
    }

    public function & __get($name)
    {
        $this->initializerb4055 && ($this->initializerb4055->__invoke($valueHolder5c912, $this, '__get', ['name' => $name], $this->initializerb4055) || 1) && $this->valueHolder5c912 = $valueHolder5c912;

        if (isset(self::$publicProperties3f2b9[$name])) {
            return $this->valueHolder5c912->$name;
        }

        $realInstanceReflection = new \ReflectionClass('WP_Rocket\\Engine\\CriticalPath\\Admin\\Settings');

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

        $realInstanceReflection = new \ReflectionClass('WP_Rocket\\Engine\\CriticalPath\\Admin\\Settings');

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

        $realInstanceReflection = new \ReflectionClass('WP_Rocket\\Engine\\CriticalPath\\Admin\\Settings');

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

        $realInstanceReflection = new \ReflectionClass('WP_Rocket\\Engine\\CriticalPath\\Admin\\Settings');

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
        \Closure::bind(function (\WP_Rocket\Engine\CriticalPath\Admin\Settings $instance) {
            unset($instance->beacon, $instance->options, $instance->critical_css);
        }, $this, 'WP_Rocket\\Engine\\CriticalPath\\Admin\\Settings')->__invoke($this);

        \Closure::bind(function (\WP_Rocket\Abstract_Render $instance) {
            unset($instance->template_path);
        }, $this, 'WP_Rocket\\Abstract_Render')->__invoke($this);
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
