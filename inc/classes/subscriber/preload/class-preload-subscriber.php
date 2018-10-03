<?php
namespace WP_Rocket\Subscriber\Preload;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Preload\Homepage;

/**
 * Preload Subscriber
 *
 * @since 3.2
 * @author Remy Perona
 */
class Preload_Subscriber implements Subscriber_Interface {
	/**
	 * Homepage Preload instance
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @var Homepage
	 */
	private $homepage_preloader;

	/**
	 * WP Rocket Options instance.
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Constructor
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @param Homepage     $homepage_preloader Homepage Preload instance.
	 * @param Options_Data $options            WP Rocket Options instance.
	 */
	public function __construct( Homepage $homepage_preloader, Options_Data $options ) {
		$this->homepage_preloader = $homepage_preloader;
		$this->options            = $options;
	}

	/**
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		return [
			'admin_notices'                   => [
				[ 'notice_preload_running' ],
				[ 'notice_preload_complete' ],
			],
			'admin_post_rocket_stop_preload'  => [ 'do_admin_post_stop_preload' ],
			'rocket_purge_time_event'         => [ 'run_preload', 11 ],
			'pagely_cache_purge_after'        => [ 'run_preload', 11 ],
			'update_option_' . WP_ROCKET_SLUG => [
				[ 'maybe_launch_preload', 11, 2 ],
				[ 'maybe_cancel_preload', 10, 2 ],
			],
		];
	}

	/**
	 * Launches the homepage preload
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @param string $lang The language code to preload.
	 * @return void
	 */
	protected function preload( $lang = '' ) {
		$urls = get_rocket_i18n_uri();

		if ( $lang ) {
			$urls = (array) get_rocket_i18n_home_url( $lang );
		}

		$this->homepage_preloader->preload( $urls );
	}

	/**
	 * Launches the homepage preload if the option is active
	 *
	 * @since 3.2
	 * @author Remy Perona
	 */
	public function run_preload() {
		if ( ! $this->options->get( 'manual_preload' ) ) {
			return;
		}

		$this->preload();
	}

	/**
	 * Cancels any preload currently running if the option is deactivated
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @param array $old_value Previous option values.
	 * @param array $value     New option values.
	 * @return void
	 */
	public function maybe_cancel_preload( $old_value, $value ) {
		if ( isset( $old_value['manual_preload'], $value['manual_preload'] ) && $old_value['manual_preload'] !== $value['manual_preload'] && 0 === (int) $value['manual_preload'] ) {
			$this->homepage_preloader->cancel_preload();
		}
	}

	/**
	 * Launches the preload if the option is activated
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @param array $old_value Previous option values.
	 * @param array $value     New option values.
	 * @return void
	 */
	public function maybe_launch_preload( $old_value, $value ) {
		if ( isset( $old_value['manual_preload'], $value['manual_preload'] ) && $old_value['manual_preload'] !== $value['manual_preload'] && 1 === (int) $value['manual_preload'] ) {
			$this->preload();
		}
	}

	/**
	 * This notice is displayed when the sitemap preload is running
	 *
	 * @since 3.2
	 * @author Remy Perona
	 */
	public function notice_preload_running() {
		$screen = get_current_screen();

		// This filter is documented in inc/admin-bar.php.
		if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
			return;
		}

		if ( 'settings_page_wprocket' !== $screen->id ) {
			return;
		}

		$running = get_transient( 'rocket_preload_running' );

		if ( false === $running ) {
			return;
		}

		\rocket_notice_html(
			[
				// translators: %1$d = Number of pages preloaded.
				'message'     => sprintf( __( 'Preload: %1$d uncached pages have now been preloaded. (refresh to see progress)', 'rocket' ), $running ),
				'dismissible' => 'notice-preload-running',
				'action'      => 'stop_preload',
			]
		);
	}

	/**
	 * This notice is displayed after the sitemap preload is complete
	 *
	 * @since 3.2
	 * @author Remy Perona
	 */
	public function notice_preload_complete() {
		$screen = get_current_screen();

		/** This filter is documented in inc/admin-bar.php */
		if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
			return;
		}

		if ( 'settings_page_wprocket' !== $screen->id ) {
			return;
		}

		$result = get_transient( 'rocket_preload_complete' );

		if ( false === $result ) {
			return;
		}

		delete_transient( 'rocket_preload_complete' );

		\rocket_notice_html(
			[
				// translators: %d is the number of pages preloaded.
				'message' => sprintf( __( 'Preload: %d pages have been cached.', 'rocket' ), $result ),
			]
		);
	}

	/**
	 * Stops currently running preload from the notice action button
	 *
	 * @since 3.2
	 * @author Remy Perona
	 */
	public function do_admin_post_stop_preload() {
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'rocket_stop_preload' ) ) {
			wp_nonce_ays( '' );
		}

		$this->homepage_preloader->cancel_preload();

		wp_safe_redirect( wp_get_referer() );
		die();
	}
}
