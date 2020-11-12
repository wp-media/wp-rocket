<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\DeferJS;

use WP_Rocket\Admin\Options_Data;

class DeferJS {
	/**
	 * Options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Instantiate the class
	 *
	 * @param Options_Data $options Options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Add the exclude defer JS option in WP Rocket options array
	 *
	 * @since 3.8
	 *
	 * @param array $options WP Rocket options array.
	 * @return array
	 */
	public function add_option( array $options ) : array {
		$options['exclude_defer_js'] = [];

		return $options;
	}
}
