<?php
namespace WP_Rocket\Storage;

use WP_Filesystem_Base;

/**
 * Abstract storage implementation.
 *
 * @since  3.9
 */
abstract class Abstract_Storage {

	/**
	 * The filesystem implementation.
	 *
	 * @var    WP_Filesystem_Base
	 * @since  3.9
	 */
	private $fs;

	/**
	 * Constructor.
	 *
	 * @since  3.9
	 */
	final public function __construct( WP_Filesystem_Base $fs ) {
		$this->fs = $fs;
	}

	/**
	 * Outputs a file.
	 *
	 * @since  3.9
	 */
	abstract public function readfile( $filename, $use_include_path = false, $context = null );

	/**
	 * Output a gz-file.
	 *
	 * @since  3.9
	 */
	abstract public function readgzfile( $filename, $use_include_path = 0 );

	/**
	 * Proxy calls to `$fs` implementation.
	 */
	public function __call( $name, $arguments) {
		return $this->fs->{$name}(...$arguments);
	}
}
