<?php
namespace WP_Rocket\Admin;

/**
 * Manages the data inside an option.
 *
 * @since 3.0
 * @author Remy Perona
 */
class Options_Data {
	/**
	 * Option data
	 *
	 * @var Array Array of data inside the option
	 */
	private $options;

	/**
	 * Constructor
	 *
	 * @param Array $options Array of data coming from an option.
	 */
	public function __construct( $options ) {
		$this->options = $options;
	}

	/**
	 * Checks if the provided key exists in the option data array.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param string $key key name.
	 * @return boolean true if it exists, false otherwise
	 */
	public function has( $key ) {
		return isset( $this->options[ $key ] );
	}

	/**
	 * Gets the value associated with a specific key.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param string $key key name.
	 * @param mixed  $default default value to return if key doesn't exist.
	 * @return mixed
	 */
	public function get( $key, $default = '' ) {
		/**
		 * Pre-filter any WP Rocket option before read
		 *
		 * @since 2.5
		 *
		 * @param mixed $default The default value.
		*/
		$value = apply_filters( 'pre_get_rocket_option_' . $key, null, $default );

		if ( null !== $value ) {
			return $value;
		}

		if ( 'consumer_key' === $key && defined( 'WP_ROCKET_KEY' ) ) {
			return WP_ROCKET_KEY;
		} elseif ( 'consumer_email' === $key && defined( 'WP_ROCKET_EMAIL' ) ) {
			return WP_ROCKET_EMAIL;
		}

		if ( ! $this->has( $key ) ) {
			return $default;
		}

		/**
		 * Filter any WP Rocket option after read
		 *
		 * @since 2.5
		 *
		 * @param mixed $default The default value.
		*/
		return apply_filters( 'get_rocket_option_' . $key, $this->options[ $key ], $default );
	}

	/**
	 * Sets the value associated with a specific key.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param string $key key name.
	 * @param mixed  $value to set.
	 * @return void
	 */
	public function set( $key, $value ) {
		$this->options[ $key ] = $value;
	}

	/**
	 * Sets multiple values.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param array $options An array of key/value pairs to set.
	 * @return void
	 */
	public function set_values( $options ) {
		foreach ( $options as $key => $value ) {
			$this->set( $key, $value );
		}
	}

	/**
	 * Gets the option array.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return array
	 */
	public function get_options() {
		return $this->options;
	}
}
