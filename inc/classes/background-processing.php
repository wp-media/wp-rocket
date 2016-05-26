<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

require( WP_ROCKET_VENDORS_PATH . 'wp-async-request.php' );
require( WP_ROCKET_VENDORS_PATH . 'wp-background-process.php' );

class Rocket_Sitemap_Preload_Process extends WP_Background_Process {

	/**
	 * @var string
	 */
	protected $action = 'rocket_sitemap_preload';

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $item Queue item to iterate over
	 *
	 * @return mixed
	 */
	protected function task( $item ) {
        $args = array(
            'timeout'    => 0.01,
            'blocking'   => false,
            'user-agent' => 'wprocketbot',
            'sslverify'  => false
        );

        $tmp = wp_remote_get( esc_url_raw( $item ), $args );
        usleep( get_rocket_option( 'sitemap_preload_url_crawl', '500000' ) );

		return false;
	}

	/**
	 * Complete
	 *
	 */
	protected function complete() {
		parent::complete();
	}

}

$GLOBALS['rocket_sitemap_background_process'] = new Rocket_Sitemap_Preload_Process();