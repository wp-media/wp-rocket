<?php

namespace WP_Rocket\Engine\Preload;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Preload Subscriber
 *
 * @since 3.2
 * @author Remy Perona
 */
class PreloadSubscriber implements Subscriber_Interface {

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
	 * Constructor.
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
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.2
	 * @author Remy Perona
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'admin_notices'                          => [
				[ 'notice_preload_triggered' ],
				[ 'notice_preload_running' ],
				[ 'notice_preload_complete' ],
			],
			'admin_post_rocket_stop_preload'         => [ 'do_admin_post_stop_preload' ],
			'pagely_cache_purge_after'               => [ 'run_preload', 11 ],
			'update_option_' . WP_ROCKET_SLUG        => [
				[ 'maybe_launch_preload', 11, 2 ],
				[ 'maybe_cancel_preload', 10, 2 ],
			],
			'rocket_after_preload_after_purge_cache' => [
				[ 'maybe_preload_mobile_homepage', 10, 3 ],
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

		delete_transient( 'rocket_preload_errors' );
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
			delete_transient( 'rocket_preload_errors' );
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
	 * After automatically preloading the homepage (after purging the cache), also preload the homepage for mobile.
	 *
	 * @since  3.5
	 * @author Grégory Viguier
	 *
	 * @param string $home_url URL to the homepage being preloaded.
	 * @param string $lang     The lang of the homepage.
	 * @param array  $args     Arguments used for the preload request.
	 */
	public function maybe_preload_mobile_homepage( $home_url, $lang, $args ) {
		if ( ! $this->homepage_preloader->is_mobile_preload_enabled() ) {
			return;
		}

		if ( empty( $args['user-agent'] ) ) {
			$args['user-agent'] = 'WP Rocket/Homepage_Preload_After_Purge_Cache';
		}

		$args['user-agent'] = $this->homepage_preloader->get_mobile_user_agent_prefix() . ' ' . $args['user-agent'];

		wp_safe_remote_get( $home_url, $args );
	}

	/**
	 * This notice is displayed when the preload is triggered from a different page than WP Rocket settings page
	 *
	 * @since 3.2
	 * @author Remy Perona
	 */
	public function notice_preload_triggered() {
		if ( ! current_user_can( 'rocket_preload_cache' ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( 'settings_page_wprocket' === $screen->id ) {
			return;
		}

		if ( false === get_transient( 'rocket_preload_triggered' ) ) {
			return;
		}

		delete_transient( 'rocket_preload_triggered' );

		$message = __( 'Preload: WP Rocket has started preloading your website.', 'rocket' );

		if ( current_user_can( 'rocket_manage_options' ) ) {
			$message .= ' ' . sprintf(
				// Translators: %1$s = opening link tag, %2$s = closing link tag.
				__( 'Go to the %1$sWP Rocket settings%2$s page to track progress.', 'rocket' ),
				'<a href="' . esc_url( admin_url( 'options-general.php?page=' . WP_ROCKET_PLUGIN_SLUG ) ) . '">',
				'</a>'
			);
		}

		\rocket_notice_html(
			[
				'status'  => 'info',
				'message' => $message,
			]
		);
	}

	/**
	 * This notice is displayed when the preload is running
	 *
	 * @since 3.2
	 * @author Remy Perona
	 */
	public function notice_preload_running() {
		if ( ! current_user_can( 'rocket_preload_cache' ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( 'settings_page_wprocket' !== $screen->id ) {
			return;
		}

		$homepage_count = get_transient( 'rocket_homepage_preload_running' );
		$sitemap_count  = get_transient( 'rocket_sitemap_preload_running' );

		if ( false === $homepage_count && false === $sitemap_count ) {
			return;
		}

		$running = $homepage_count + $sitemap_count;
		$status  = 'info';
		// translators: %1$s = Number of pages preloaded.
		$message  = '<p>' . sprintf( _n( 'Preload: %1$s uncached page has now been preloaded. (refresh to see progress)', 'Preload: %1$s uncached pages have now been preloaded. (refresh to see progress)', $running, 'rocket' ), number_format_i18n( $running ) );
		$message .= ' <em> - (' . date_i18n( get_option( 'date_format' ) ) . ' @ ' . date_i18n( get_option( 'time_format' ) ) . ') </em></p>';

		if ( defined( 'WP_ROCKET_DEBUG' ) && WP_ROCKET_DEBUG ) {

			$errors = get_transient( 'rocket_preload_errors' );

			if ( false !== $errors ) {
				$status   = 'warning';
				$message .= '<p>' . _n( 'The following error happened during gathering of the URLs to preload:', 'The following errors happened during gathering of the URLs to preload:', count( $errors['errors'] ), 'rocket' ) . '</p>';

				foreach ( $errors['errors'] as $error ) {
					$message .= '<p>' . $error . '</p>';
				}
			}
		}

		\rocket_notice_html(
			[
				'status'      => $status,
				'message'     => $message,
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
		if ( ! current_user_can( 'rocket_preload_cache' ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( 'settings_page_wprocket' !== $screen->id ) {
			return;
		}

		$result = get_transient( 'rocket_preload_complete' );

		if ( false === $result ) {
			return;
		}

		$result_timestamp = get_transient( 'rocket_preload_complete_time' );

		if ( false === $result_timestamp ) {
			return;
		}

		delete_transient( 'rocket_preload_complete' );
		delete_transient( 'rocket_preload_errors' );
		delete_transient( 'rocket_preload_complete_time' );

		// translators: %d is the number of pages preloaded.
		$notice_message  = sprintf( __( 'Preload complete: %d pages have been cached.', 'rocket' ), $result );
		$notice_message .= ' <em> (' . $result_timestamp . ') </em>';

		\rocket_notice_html(
			[
				'message' => $notice_message,
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
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'rocket_stop_preload' ) ) {
			wp_nonce_ays( '' );
		}

		if ( ! current_user_can( 'rocket_preload_cache' ) ) {
			wp_safe_redirect( wp_get_referer() );
			die();
		}

		$this->homepage_preloader->cancel_preload();

		wp_safe_redirect( wp_get_referer() );
		die();
	}
}
