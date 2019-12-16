<?php
/**
 * Common functionality for all PHP test cases.
 *
 * @package WP_Rocket\Tests
 */

namespace WP_Rocket\Tests;

trait TestCaseTrait {
	/**
	 * Get reflective access to the private/protected method.
	 *
	 * @param string $method_name Method name for which to gain access.
	 * @param string $class_name  Name of the target class.
	 *
	 * @return \ReflectionMethod
	 * @throws \ReflectionException Throws an exception if method does not exist.
	 */
	protected function get_reflective_method( $method_name, $class_name ) {
		$class  = new \ReflectionClass( $class_name );
		$method = $class->getMethod( $method_name );
		$method->setAccessible( true );

		return $method;
	}

	/**
	 * Get reflective access to the private/protected property.
	 *
	 * @param string       $property Property name for which to gain access.
	 * @param string|mixed $class    Class name or instance.
	 *
	 * @return \ReflectionProperty|string
	 * @throws \ReflectionException Throws an exception if property does not exist.
	 */
	protected function get_reflective_property( $property, $class ) {
		$class    = new \ReflectionClass( $class );
		$property = $class->getProperty( $property );
		$property->setAccessible( true );

		return $property;
	}

	/**
	 * Set the value of a property or private property.
	 *
	 * @param mixed  $value    The value to set for the property.
	 * @param string $property Property name for which to gain access.
	 * @param mixed  $instance Instance of the target object.
	 *
	 * @return \ReflectionProperty|string
	 * @throws \ReflectionException Throws an exception if property does not exist.
	 */
	protected function set_reflective_property( $value, $property, $instance ) {
		$property = $this->get_reflective_property( $property, $instance );
		$property->setValue( $instance, $value );
		$property->setAccessible( false );

		return $property;
	}
}
