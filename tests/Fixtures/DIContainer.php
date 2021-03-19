<?php

namespace WP_Rocket\Tests\Fixtures;

use ArrayAccess;
use WP_Rocket\Engine\Cache\AdvancedCache;

class DIContainer implements ArrayAccess {
	private $container = [];

	public function setUp() : void {
		add_filter( 'rocket_container', [ $this, 'getContainer' ] );
	}

	public function tearDown() {
		remove_filter( 'rocket_container', [ $this, 'getContainer' ] );
	}

	public function getContainer() {
		return $this;
	}

	public function add( $key, $concrete ) {
		$this->container[ $key ] = $concrete;
	}

	public function get( $key ) {
		if ( $this->has( $key ) ) {
			return $this->container[ $key ];
		}
	}

	public function has( $key ) {
		return array_key_exists( $key, $this->container );
	}

	public function remove( $key ) {
		unset( $this->container[ $key ] );
	}

	public function offsetExists( $key ) {
		return $this->has( $key );
	}

	public function offsetGet( $key ) {
		return $this->get( $key );
	}

	public function offsetSet( $key, $concrete ) {
		$this->add( $key, $concrete );
	}

	public function offsetUnset( $key ) {
		return $this->remove( $key );
	}

	/********************************************************
	 * Add specific concretes here for reuse in the tests.
	 *******************************************************/

	public function addAdvancedCache( $template_path, $filesystem = null ) {
		$this->container['advanced_cache'] = new AdvancedCache( $template_path, $filesystem );
	}
}
