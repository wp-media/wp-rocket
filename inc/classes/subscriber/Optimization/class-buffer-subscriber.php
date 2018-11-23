<?php
namespace WP_Rocket\Subscriber\Optimization;

use WP_Rocket\Buffer\Optimization;
use WP_Rocket\Event_Management\Subscriber_Interface;

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Event subscriber to buffer and process a page content.
 *
 * @since  3.3
 * @author Grégory Viguier
 */
class Buffer_Subscriber implements Subscriber_Interface {

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'template_include' => [ 'start_content_process', PHP_INT_MAX - 9 ],
		];
	}

	/**
	 * Start buffering the page content before including its template.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  string $template The path of the template to include.
	 * @return string
	 */
	public function start_content_process( $template ) {
		( new Optimization(
			[
				'config_dir_path' => WP_ROCKET_CONFIG_PATH,
			]
		) )->maybe_init_process();

		return $template;
	}
}
