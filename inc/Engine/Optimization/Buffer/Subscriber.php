<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\Buffer;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Event subscriber to buffer and process a page content.
 *
 * @since 3.3
 */
class Subscriber implements Subscriber_Interface {
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
			'rocket_buffer' => [ 'insert_rocket_head', 100000 ],
		];
	}

	/**
	 * Start buffering the page content and apply optimizations if we can.
	 *
	 * @since 3.3
	 */
	public function start_content_process() {
		return $this->optimizer->maybe_init_process();
	}

	/**
	 * Insert rocket_head into the buffer HTML
	 *
	 * @param string $html Buffer HTML.
	 * @return string
	 */
	public function insert_rocket_head( $html ) {
		if ( empty( $html ) ) {
			return $html;
		}

		$filtered_buffer = preg_replace(
			'#</title>#iU',
			'</title>' . wpm_apply_filters_typed( 'string', 'rocket_head', '' ),
			$html,
			1
		);

		if ( empty( $filtered_buffer ) ) {
			return $html;
		}

		return $filtered_buffer;
	}
}
