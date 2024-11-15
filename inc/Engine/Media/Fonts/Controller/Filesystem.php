<?php

declare(strict_types=1);

namespace WP_Rocket\Engine\Media\Fonts\Controller;

use WP_Rocket\Engine\Common\AbstractFileSystem;
use WP_Filesystem_Direct;

class Filesystem extends AbstractFileSystem {

	/**
	 * Path to the fonts storage
	 *
	 * @var string
	 */
	private $path; // @phpstan-ignore-line

	/**
	 * Instantiate the class
	 *
	 * @param string               $base_path Base path to the fonts storage.
	 * @param WP_Filesystem_Direct $filesystem WP Filesystem instance.
	 */
	public function __construct( $base_path, $filesystem = null ) {
		parent::__construct( is_null( $filesystem ) ? rocket_direct_filesystem() : $filesystem );

		$this->path = $base_path . get_current_blog_id() . '/';
	}
}
