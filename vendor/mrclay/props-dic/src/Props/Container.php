<?php

namespace Props;

use Interop\Container\ContainerInterface;

/**
 * Container holding values which can be resolved upon reading and optionally stored and shared
 * across reads.
 *
 * Values are read/set as properties.
 *
 * @note see scripts/example.php
 */
class Container implements ContainerInterface
{
    /**
     * @var callable[]
     */
    private $factories = array();

    /**
     * @var array
     */
    private $cache = array();

    /**
     * Fetch a value.
     *
     * @param string $name
     * @return mixed
     * @throws FactoryUncallableException|ValueUnresolvableException|NotFoundException
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->cache)) {
            return $this->cache[$name];
        }
        $value = $this->build($name);
        $this->cache[$name] = $value;
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        return $this->__get($name);
    }

    /**
     * Set a value.
     *
     * @param string $name
     * @param mixed $value
     * @throws \InvalidArgumentException
     */
    public function __set($name, $value)
    {
        if ($value instanceof \Closure) {
            $this->setFactory($name, $value);
            return;
        }

        $this->cache[$name] = $value;
        unset($this->factories[$name]);
    }

    /**
     * Set a value to be later returned as is. You only need to use this if you wish to store
     * a Closure.
     *
     * @param string $name
     * @param mixed $value
     * @throws \InvalidArgumentException
     */
    public function setValue($name, $value)
    {
        unset($this->factories[$name]);
        $this->cache[$name] = $value;
    }

    /**
     * @param string $name
     */
    public function __unset($name)
    {
        unset($this->cache[$name]);
        unset($this->factories[$name]);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->factories) || array_key_exists($name, $this->cache);
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return $this->__isset($name);
    }

    /**
     * Fetch a freshly-resolved value.
     *
     * @param string $method method name must start with "new_"
     * @param array $args
     * @return mixed
     * @throws BadMethodCallException
     */
    public function __call($method, $args)
    {
        if (0 !== strpos($method, 'new_')) {
            throw new BadMethodCallException("Method name must begin with 'new_'");
        }

        return $this->build(substr($method, 4));
    }

    /**
     * Can we fetch a new value via new_$name()?
     *
     * @param string $name
     * @return bool
     */
    public function hasFactory($name)
    {
        return array_key_exists($name, $this->factories);
    }

    /**
     * Set a factory to generate a value when the container is read.
     *
     * @param string   $name    The name of the value
     * @param callable $factory Factory for the value
     * @throws FactoryUncallableException
     */
    public function setFactory($name, $factory)
    {
        if (!is_callable($factory, true)) {
            throw new FactoryUncallableException('$factory must appear callable');
        }

        unset($this->cache[$name]);
        $this->factories[$name] = $factory;
    }

    /**
     * Get an already-set factory callable (Closure, invokable, or callback)
     *
     * @param string $name The name of the value
     * @return callable
     * @throws NotFoundException
     */
    public function getFactory($name)
    {
        if (!array_key_exists($name, $this->factories)) {
            throw new NotFoundException("No factory available for: $name");
        }

        return $this->factories[$name];
    }

    /**
     * Add a function that gets applied to the return value of an existing factory
     *
     * @note A cached value (from a previous property read) will thrown away. The next property read
     *       (and all new_NAME() calls) will call the original factory.
     *
     * @param string   $name     The name of the value
     * @param callable $extender Function that is applied to extend the returned value
     * @return \Closure
     * @throws FactoryUncallableException|NotFoundException
     */
    public function extend($name, $extender)
    {
        if (!is_callable($extender, true)) {
            throw new FactoryUncallableException('$extender must appear callable');
        }

        if (!array_key_exists($name, $this->factories)) {
            throw new NotFoundException("No factory available for: $name");
        }

        $factory = $this->factories[$name];

        $newFactory = function (Container $c) use ($extender, $factory) {
            return call_user_func($extender, call_user_func($factory, $c), $c);
        };

        $this->setFactory($name, $newFactory);

        return $newFactory;
    }

    /**
     * Get all keys available
     *
     * @return string[]
     */
    public function getKeys()
    {
        $keys = array_keys($this->cache) + array_keys($this->factories);
        return array_unique($keys);
    }

    /**
     * Build a value
     *
     * @param string $name
     * @return mixed
     * @throws FactoryUncallableException|ValueUnresolvableException|NotFoundException
     */
    private function build($name)
    {
        if (!array_key_exists($name, $this->factories)) {
            throw new NotFoundException("Missing value: $name");
        }

        $factory = $this->factories[$name];

        if (is_callable($factory)) {
            try {
                return call_user_func($factory, $this);
            } catch (\Exception $e) {
                throw new ValueUnresolvableException("Factory for '$name' threw an exception.", 0, $e);
            }
        }

        $msg = "Factory for '$name' was uncallable";
        if (is_string($factory)) {
            $msg .= ": '$factory'";
        } elseif (is_array($factory)) {
            if (is_string($factory[0])) {
                $msg .= ": '{$factory[0]}::{$factory[1]}'";
            } else {
                $msg .= ": " . get_class($factory[0]) . "->{$factory[1]}";
            }
        }
        throw new FactoryUncallableException($msg);
    }
}
