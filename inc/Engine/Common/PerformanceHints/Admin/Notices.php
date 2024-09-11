<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\Admin;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Common\Context\ContextInterface;

class Notices {
	/**
	 * Array of factories
	 *
	 * @var array
	 */
	private $factories;

	/**
	 * Constructor
	 *
	 * @param array $factories Array of factories.
	 */
	public function __construct( array $factories ) {
		$this->factories = $factories;
	}

	/**
	 * Show admin notice after clearing performance hints tables.
	 *
	 * @return void
	 */
	public function clean_performance_hint_result() {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		if ( empty( $this->factories ) ) {
			return;
		}

		$response = get_transient( 'rocket_performance_hints_clear_message' );

		if ( ! $response ) {
			return;
		}

		delete_transient( 'rocket_performance_hints_clear_message' );

		rocket_notice_html( $response );
	}
}
