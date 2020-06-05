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
	use TransientTrait;

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
	 * ProcessorService instance.
	 *
	 * @var ProcessorService
	 */
	protected $processor;

	/**
	 * Instantiate the class
	 *
	 * @param ProcessorService $processor ProcessorService instance.
	 */
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
		if ( ! is_array( $item ) ) {
			return false;
		}

		$transient = get_transient( 'rocket_critical_css_generation_process_running' );
		$mobile    = isset( $item['mobile'] ) ? $item['mobile'] : 0;

		$generation_params = [
			'is_mobile' => $mobile,
			'item_type' => $item['type'],
		];
		$generated         = $this->processor->process_generate( $item['url'], $item['path'], $generation_params );

		if ( is_wp_error( $generated ) ) {
			$this->update_running_transient( $transient, $item['path'], $mobile, $generated->get_error_message(), false );
			return false;
		}

		if ( isset( $generated['code'] ) && 'cpcss_generation_pending' === $generated['code'] ) {
			$pending = get_transient( 'rocket_cpcss_generation_pending' );

			if ( false === $pending ) {
				$pending = [];
			}

			$pending[ $item['path'] ] = $item;

			set_transient( 'rocket_cpcss_generation_pending', $pending, HOUR_IN_SECONDS );

			return false;
		}

		$this->update_running_transient( $transient, $item['path'], $mobile, $generated['message'], ( 'cpcss_generation_successful' === $generated['code'] ) );
		return false;
	}
}
