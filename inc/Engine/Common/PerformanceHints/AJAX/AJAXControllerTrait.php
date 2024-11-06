<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\AJAX;

trait AJAXControllerTrait {
	/**
	 * Get status code and message to be saved into the database
	 *
	 * @param string $status Current status code from $_POST.
	 * @return array
	 */
	protected function get_status_code_message( string $status ): array {
		$status_code    = 'success' !== $status ? 'failed' : 'completed';
		$status_message = '';

		switch ( $status ) {
			case 'script_error':
				$status_message = esc_html__( 'Script error', 'rocket' );
				break;
			case 'timeout':
				$status_message = esc_html__( 'Script timeout', 'rocket' );
				break;
		}

		return [
			$status_code,
			$status_message,
		];
	}
}
