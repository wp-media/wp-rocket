<?php

namespace WP_Rocket\Engine\CriticalPath;

use WP_Background_Process;

/**
 * Extends the background process class for the critical CSS generation process.
 *
 * @since 2.11
 *
 * @see WP_Background_Process
 */
class CriticalCSSGeneration extends WP_Background_Process {
	/**
	 * Process prefix.
	 *
	 * @var string
	 */
	protected $prefix = 'rocket';

	/**
	 * Specific action identifier for sitemap preload.
	 *
	 * @var string Action identifier
	 */
	protected $action = 'critical_css_generation';

	/**
	 * ProcessorService instance
	 *
	 * @var ProcessorService
	 */
	protected $processor;

	public function __construct( ProcessorService $processor ) {
		parent::__construct();

		$this->processor = $processor;
	}

	/**
	 * Perform the optimization corresponding to $item.
	 *
	 * @since 2.11
	 *
	 * @param mixed $item Queue item to iterate over.
	 *
	 * @return bool false if task performed successfully, true otherwise to re-queue the item.
	 */
	protected function task( $item ) {
		$timeout   = false;
		$transient = get_transient( 'rocket_critical_css_generation_process_running' );

		if ( 10 < $item['check'] ) {
			$timeout = true;
		}

		$generated = $this->processor->process_generate( $item['url'], $item['path'], $timeout, $item['mobile'] );

		if ( is_wp_error( $generated ) ) {
			$transient['items'][] = $generated->get_error_message( 'cpcss_generation_failed' );
			set_transient( 'rocket_critical_css_generation_process_running', $transient, HOUR_IN_SECONDS );

			return false;
		}

		if ( isset( $generated['code'] ) && 'cpcss_generation_pending' === $generated['code'] ) {
			$item['check']++;

			return $item;
		}

		$transient['items'][] = $generated['message'];
		$transient['generated']++;
		set_transient( 'rocket_critical_css_generation_process_running', $transient, HOUR_IN_SECONDS );

		return false;
	}

	/**
	 * Launches when the background process is complete.
	 *
	 * @since 2.11
	 */
	protected function complete() {
		/**
		 * Fires when the critical CSS generation process is complete.
		 *
		 * @since 2.11
		 */
		do_action( 'rocket_critical_css_generation_process_complete' );

		set_transient( 'rocket_critical_css_generation_process_complete', get_transient( 'rocket_critical_css_generation_process_running' ), HOUR_IN_SECONDS );
		delete_transient( 'rocket_critical_css_generation_process_running' );

		parent::complete();
	}
}
