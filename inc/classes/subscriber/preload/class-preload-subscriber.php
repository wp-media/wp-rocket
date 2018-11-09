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
				[ 'notice_preload_triggered'],
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
		if ( $lang ) {
			$urls = (array) get_rocket_i18n_home_url( $lang );
		} else {
			$urls = get_rocket_i18n_uri();
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
		if ( $this->homepage_preloader->is_process_running() ) {
			return;
		}

		// These values are ignored because they don't impact the cache content.
		$ignored_options = [
			'cache_mobile'                => true,
			'purge_cron_interval'         => true,
			'purge_cron_unit'             => true,
			'sitemap_preload'             => true,
			'sitemaps'                    => true,
			'database_revisions'          => true,
			'database_auto_drafts'        => true,
			'database_trashed_posts'      => true,
			'database_spam_comments'      => true,
			'database_trashed_comments'   => true,
			'database_expired_transients' => true,
			'database_all_transients'     => true,
			'database_optimize_tables'    => true,
			'schedule_automatic_cleanup'  => true,
			'automatic_cleanup_frequency' => true,
			'do_cloudflare'               => true,
			'cloudflare_email'            => true,
			'cloudflare_api_key'          => true,
			'cloudflare_zone_id'          => true,
			'cloudflare_devmode'          => true,
			'cloudflare_auto_settings'    => true,
			'cloudflare_old_settings'     => true,
			'heartbeat_admin_behavior'    => true,
			'heartbeat_editor_behavior'   => true,
			'varnish_auto_purge'          => true,
			'do_beta'                     => true,
			'analytics_enabled'           => true,
			'sucury_waf_cache_sync'       => true,
			'sucury_waf_api_key'          => true,
		];

		// Create 2 arrays to compare.
		$old_value_diff = array_diff_key( $old_value, $ignored_options );
		$value_diff     = array_diff_key( $value, $ignored_options );

		// If it's different, preload.
		if ( md5( wp_json_encode( $old_value_diff ) ) === md5( wp_json_encode( $value_diff ) ) ) {
			return;
		}

		if ( isset( $value['manual_preload'] ) && 1 === (int) $value['manual_preload'] ) {
			$this->preload();
		}
	}

	/**
	 * This notice is displayed when the preload is triggered from a different page than WP Rocket settings page
	 *
	 * @since 3.2
	 * @author Remy Perona
	 */
	public function notice_preload_triggered() {
		$screen = get_current_screen();

		// This filter is documented in inc/admin-bar.php.
		if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
			return;
		}

		if ( 'settings_page_wprocket' === $screen->id ) {
			return;
		}

		if ( false === get_transient( 'rocket_preload_triggered' ) ) {
			return;
		}

		delete_transient( 'rocket_preload_triggered' );

		\rocket_notice_html(
			[
				'status'  => 'info',
				'message' => sprintf(
					// Translators: %1$s = opening link tag, %2$s = closing link tag.
					__( 'Preload: WP Rocket has started preloading your website. Go to the %1$sWP Rocket settings%2$s page to track progress.', 'rocket' ),
					'<a href="' . esc_url( admin_url( 'options-general.php?page=' . WP_ROCKET_PLUGIN_SLUG ) ) . '">',
					'</a>'
				),
			]
		);
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

		/** This filter is documented in inc/admin-bar.php */
		if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
			wp_safe_redirect( wp_get_referer() );
			die();
		}

		$this->homepage_preloader->cancel_preload();

		wp_safe_redirect( wp_get_referer() );
		die();
	}
}
