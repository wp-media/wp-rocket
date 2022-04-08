<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Frontend;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;

class Subscriber implements Subscriber_Interface {

	/**
	 * UsedCss instance
	 *
	 * @var UsedCSS
	 */
	private $used_css;

	/**
	 * Instantiate the class
	 *
	 * @param UsedCSS $used_css UsedCSS instance.
	 */
	public function __construct( UsedCSS $used_css ) {
		$this->used_css = $used_css;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'rocket_buffer'                  => [ 'treeshake', 1000 ],
			'rocket_disable_preload_fonts'   => 'maybe_disable_preload_fonts',
			'rocket_rucss_pending_jobs_cron' => 'process_pending_jobs',
			'rocket_rucss_job_check_status'  => 'check_job_status',
		];
	}

	/**
	 * Apply TreeShaked CSS to the current HTML page.
	 *
	 * @param string $html  HTML content.
	 *
	 * @return string  HTML content.
	 */
	public function treeshake( string $html ): string {
		return $this->used_css->treeshake( $html );
	}

	/**
	 * Disables the preload fonts if RUCSS is enabled
	 *
	 * @since 3.9
	 *
	 * @param bool $value Value for the disable preload fonts filter.
	 *
	 * @return bool
	 */
	public function maybe_disable_preload_fonts( $value ): bool {
		if ( $this->used_css->is_allowed() ) {
			return true;
		}

		return $value;
	}

	/**
	 * Process pending jobs with Cron iteration.
	 *
	 * @return void
	 */
	public function process_pending_jobs() {
		$this->used_css->process_pending_jobs();
	}

	/**
	 * Handle job status by DB row ID.
	 *
	 * @param int $id DB Row ID.
	 *
	 * @return void
	 */
	public function check_job_status( int $id ) {
		$this->used_css->check_job_status( $id );
	}

}
