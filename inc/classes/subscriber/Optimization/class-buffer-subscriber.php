<?php
namespace WP_Rocket\Subscriber\Optimization;

use WP_Rocket\Buffer\Optimization;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Event subscriber to buffer and process a page content.
 *
 * @since  3.3
 * @author Grégory Viguier
 */
class Buffer_Subscriber implements Subscriber_Interface {
	/**
	 * Optimization instance
	 *
	 * @var Optimization
	 */
	private $optimizer;

	/**
	 * Constructor
	 *
	 * @param Optimization $optimizer Optimization instance.
	 */
	public function __construct( Optimization $optimizer ) {
		$this->optimizer = $optimizer;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_subscribed_events() {
		return [
			'template_redirect' => [ 'start_content_process', 2 ],
		];
	}

	/**
	 * Start buffering the page content and apply optimizations if we can.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 */
	public function start_content_process() {
		return $this->optimizer->maybe_init_process();
	}
}
