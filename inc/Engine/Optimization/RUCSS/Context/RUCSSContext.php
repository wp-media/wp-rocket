<?php

namespace WP_Rocket\Engine\Optimization\RUCSS\Context;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\Context\AbstractContext;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\Filesystem;

class RUCSSContext extends AbstractContext {



	/**
	 * Filesystem instance
	 *
	 * @var Filesystem
	 */
	protected $filesystem;

	/**
	 * Instantiate the class.
	 *
	 * @param Options_Data $options Options.
	 * @param Filesystem   $filesystem Filesystem instance.
	 */
	public function __construct( Options_Data $options, Filesystem $filesystem ) {
		parent::__construct( $options );
		$this->filesystem = $filesystem;
	}


	/**
	 * Check if the operation is allowed.
	 *
	 * @param array $data Data to provide to the context.
	 * @return bool
	 */
	public function is_allowed( array $data = [] ): bool {
		$is_allowed = $this->run_common_checks(
			[
				'do_not_optimize'    => false,
				'bypass'             => false,
				'option'             => 'remove_unused_css',
				'password_protected' => false,
				'post_excluded'      => 'remove_unused_css',
				'logged_in'          => false,
			]
		);

		if ( ! $is_allowed ) {
			return false;
		}

		return $this->filesystem->is_writable_folder();
	}
}
