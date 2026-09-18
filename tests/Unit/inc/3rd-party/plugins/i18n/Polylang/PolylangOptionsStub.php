<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\plugins\i18n\Polylang;

/**
 * Stands in for the settings Polylang holds, which from 3.7 are an object that answers like an
 * array rather than an array. Casting one gives its private properties, so the code under test has
 * to read it by key.
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
