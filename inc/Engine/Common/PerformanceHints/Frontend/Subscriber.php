<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\Frontend;

use WP_Rocket\Buffer\Tests;
use WP_Rocket\Engine\Common\Utils;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {

	/**
	 * Processor Instance.
	 *
	 * @var Processor
	 */
	private $processor;

	/**
	 * Buffer tests to run against current page, to decide if we can start the buffer or not.
	 *
	 * @var Tests
	 */
	private $buffer_tests;

	/**
	 * Instantiate the class
	 *
	 * @param Processor $processor Processor Instance.
	 * @param Tests     $buffer_tests Buffer tests instance.
	 */
	public function __construct( Processor $processor, Tests $buffer_tests ) {
		$this->processor    = $processor;
		$this->buffer_tests = $buffer_tests;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'rocket_buffer'                   => [ 'maybe_apply_optimizations', 17 ],
			'rocket_performance_hints_buffer' => [ 'maybe_apply_optimizations', 17 ],
			'template_redirect'               => [ 'start_performance_hints_buffer', 3 ],
		];
	}

	/**
	 * Apply performance hints optimizations.
	 *
	 * @param string $html HTML content.
	 *
	 * @return string
	 */
	public function maybe_apply_optimizations( $html ): string {
		if ( empty( $html ) || ( ! Utils::is_saas_visit() && Utils::is_inspector_visit() ) ) {
			return $html;
		}
		return $this->processor->maybe_apply_optimizations( $html );
	}

	/**
	 * Start performance hints buffer
	 *
	 * @return void
	 */
	public function start_performance_hints_buffer() {
		if ( ! Utils::is_saas_visit() && ! Utils::is_inspector_visit() ) {
			return;
		}

		if ( ! $this->buffer_tests->can_process_any_buffer() ) {
			return;
		}

		ob_start( [ $this, 'performance_hints_buffer' ] );
	}

	/**
	 * Update images that have no width/height with real dimensions for the SaaS
	 *
	 * @param string $buffer Page HTML content.
	 *
	 * @return string Page HTML content after update.
	 */
	public function performance_hints_buffer( $buffer ) {
		/**
		 * Filters the buffer content for performance hints.
		 *
		 * @since 3.17
		 *
		 * @param $buffer string HTML content.
		 */
		return wpm_apply_filters_typed( 'string', 'rocket_performance_hints_buffer', $buffer );
	}
}
