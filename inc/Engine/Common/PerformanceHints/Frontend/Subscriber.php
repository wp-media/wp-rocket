<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\Frontend;

use WP_Rocket\Event_Management\Subscriber_Interface;


class Subscriber implements Subscriber_Interface {

	/**
	 * Processor Instance.
	 *
	 * @var Processor
	 */
	private $processor;

	/**
	 * Instantiate the class
	 *
	 * @param Processor $processor Processor Instance.
	 */
	public function __construct( Processor $processor ) {
		$this->processor = $processor;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'rocket_buffer'                           => [ 'maybe_apply_optimizations', 17 ],
			'rocket_critical_image_saas_visit_buffer' => [ 'maybe_apply_optimizations', 17 ],
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
		return $this->processor->maybe_apply_optimizations( $html );
	}
}
