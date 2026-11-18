<?php

namespace WP_Rocket\Tests\Fixtures\Polylang;

/**
 * Stands in for the settings object Polylang holds from 3.7, which reads like an array.
 */
class Polylang_Options_Stub implements \ArrayAccess {
	/**
	 * Settings held.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Constructor.
	 *
	 * @param array $options Settings held.
	 */
	public function __construct( array $options ) {
		$this->options = $options;
	}

	#[\ReturnTypeWillChange]
	public function offsetExists( $offset ) {
		return isset( $this->options[ $offset ] );
	}

	#[\ReturnTypeWillChange]
	public function offsetGet( $offset ) {
		return $this->options[ $offset ] ?? null;
	}

	#[\ReturnTypeWillChange]
	public function offsetSet( $offset, $value ) {
		$this->options[ $offset ] = $value;
	}

	#[\ReturnTypeWillChange]
	public function offsetUnset( $offset ) {
		unset( $this->options[ $offset ] );
	}
}
