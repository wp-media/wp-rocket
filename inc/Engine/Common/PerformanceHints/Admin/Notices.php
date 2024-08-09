<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\Admin;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Common\Context\ContextInterface;

class Notices {
	/**
	 * Context instance
	 *
	 * @var ContextInterface
	 */
	private $atf_context;

	/**
	 * Constructor
	 *
	 * @param ContextInterface $atf_context ATF context instance.
	 */
	public function __construct( ContextInterface $atf_context ) {
		$this->atf_context = $atf_context;
	}

	/**
	 * Show admin notice after clearing performance hints tables.
	 *
	 * @return void
	 */
	public function clean_saas_result() {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		if ( ! $this->atf_context->is_allowed() ) {
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
