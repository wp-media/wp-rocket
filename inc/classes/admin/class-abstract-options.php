<?php
namespace WP_Rocket\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Manages options using the WordPress options API.
 *
 * @since 3.0
 * @author Remy Perona
 */
abstract class Abstract_Options {
	/**
	 * Gets the option for the given name. Returns the default value if the value does not exist.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param string $name   Name of the option to get.
	 * @param mixed  $default Default value to return if the value does not exist.
	 *
	 * @return mixed
	 */
	abstract public function get( $name, $default = null );

	/**
	 * Sets the value of an option. Update the value if the option for the given name already exists.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 * @param string $name Name of the option to set.
	 * @param mixed  $value Value to set for the option.
	 *
	 * @return void
	 */
	abstract public function set( $name, $value );

	/**
	 * Deletes the option with the given name.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param string $name Name of the option to delete.
	 *
	 * @return void
	 */
	abstract public function delete( $name );

	/**
	 * Checks if the option with the given name exists.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param string $name Name of the option to check.
	 *
	 * @return boolean True if the option exists, false otherwise
	 */
	public function has( $name ) {
		return null !== $this->get( $name );
	}
}
