<?php
namespace WP_Rocket\Subscriber\Preload;

use WP_Rocket\Preload\Sitemap;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Sitemap Preload Subscriber
 *
 * @since 3.2
 * @author Remy Perona
 */
class Sitemap_Preload_Subscriber implements Subscriber_Interface {
	/**
	 * Constructor
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @param Sitemap      $sitemap_preload Sitemap Preload instance.
	 * @param Options_Data $options         Options instance.
	 */
	public function __construct( Sitemap $sitemap_preload, Options_Data $options ) {
		$this->options         = $options;
		$this->sitemap_preload = $sitemap_preload;
	}

	/**
	 *
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		return [
			'admin_notices'                   => [
				[ 'notice_sitemap_preload_running' ],
				[ 'notice_sitemap_preload_complete' ],
			],
			'rocket_purge_time_event'         => [ 'preload', 11 ],
			'pagely_cache_purge_after'        => [ 'preload', 11 ],
			'update_option_' . WP_ROCKET_SLUG => [ 'maybe_cancel_preload', 10, 2 ],
		];
	}

	/**
	 * Launches the sitemap preload
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function preload() {
		if ( ! $this->options->get( 'sitemap_preload' ) ) {
			return;
		}

		/**
		 * Filters the sitemaps list to preload
		 *
		 * @since 2.8
		 *
		 * @param array Array of sitemaps URL
		 */
		$sitemaps = apply_filters( 'rocket_sitemap_preload_list', $this->options->get( 'sitemaps', false ) );
		$sitemaps = array_flip( array_flip( $sitemaps ) );

		if ( ! $sitemaps ) {
			return;
		}

		$this->sitemap_preload->cancel_preload();
		$this->sitemap_preload->run_preload( $sitemaps );
	}

	/**
	 * This notice is displayed when the sitemap preload is running
	 *
	 * @since 3.2
	 * @author Remy Perona
	 */
	public function notice_sitemap_preload_running() {
		$screen = get_current_screen();

		// This filter is documented in inc/admin-bar.php.
		if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
			return;
		}

		if ( 'settings_page_wprocket' !== $screen->id ) {
			return;
		}

		$running = get_transient( 'rocket_sitemap_preload_running' );

		if ( false === $running ) {
			return;
		}

		\rocket_notice_html( [
			// translators: %1$d = Number of pages preloaded.
			'message'     => sprintf( __( 'Sitemap preload: %1$d uncached pages have now been preloaded. (refresh to see progress)', 'rocket' ), $running ),
			'dismissible' => 'notice-sitemap-preload-running',
		] );
	}

	/**
	 * This notice is displayed after the sitemap preload is complete
	 *
	 * @since 3.2
	 * @author Remy Perona
	 */
	public function notice_sitemap_preload_complete() {
		$screen = get_current_screen();

		/** This filter is documented in inc/admin-bar.php */
		if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
			return;
		}

		if ( 'settings_page_wprocket' !== $screen->id ) {
			return;
		}

		$result = get_transient( 'rocket_sitemap_preload_complete' );

		if ( false === $result ) {
			return;
		}

		delete_transient( 'rocket_sitemap_preload_complete' );

		\rocket_notice_html( [
			// translators: %d is the number of pages preloaded.
			'message' => sprintf( __( 'Sitemap preload: %d pages have been cached.', 'rocket' ), $result ),
		] );
	}

	/**
	 * Cancels any running sitemap preload if the option is deactivated
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @param array $old_value Previous option values.
	 * @param array $value     New option values.
	 * @return void
	 */
	public function maybe_cancel_preload( $old_value, $value ) {
		if ( isset( $old_value['sitemap_preload'], $value['sitemap_preload'] ) && $old_value['sitemap_preload'] !== $value['sitemap_preload'] && 0 === (int) $value['sitemap_preload'] ) {
			$this->sitemap_preload->cancel_preload();
		}
	}
}
