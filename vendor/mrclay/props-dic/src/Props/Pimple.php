<?php

namespace Props;

/**
 * A version of Pimple that uses property access instead of array access
 *
 * @author Steve Clay <steve@mrclay.org>
 */
class Pimple extends \Pimple\Container
{
    /**
     * Sets a parameter or an object.
     *
     * @param  string            $id    The unique identifier for the parameter or object
     * @param  mixed             $value The value of the parameter or a closure to define an object
     * @throws \RuntimeException Prevent override of a frozen service
     */
    public function __set($id, $value)
    {
        $this->offsetSet($id, $value);
    }

    /**
     * Gets a parameter or an object.
     *
     * @param string $id The unique identifier for the parameter or object
     * @return mixed The value of the parameter or an object
     * @throws \InvalidArgumentException if the identifier is not defined
     */
    public function __get($id)
    {
        return $this->offsetGet($id);
    }

    /**
     * Checks if a parameter or an object is set.
     *
     * @param string $id The unique identifier for the parameter or object
     * @return Boolean
     */
    public function __isset($id)
    {
        return $this->offsetExists($id);
    }

    /**
     * Unsets a parameter or an object.
     *
     * @param string $id The unique identifier for the parameter or object
     */
    public function __unset($id)
    {
        $this->offsetUnset($id);
    }
}
