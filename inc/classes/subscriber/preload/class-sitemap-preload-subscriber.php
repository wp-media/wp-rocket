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
			'rocket_purge_time_event'         => [ 'preload', 12 ],
			'pagely_cache_purge_after'        => [ 'preload', 12 ],
			'update_option_' . WP_ROCKET_SLUG => [ 'maybe_cancel_preload', 10, 2 ],
			'admin_notices'                   => [ 'simplexml_notice' ],
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
		if ( ! $this->options->get( 'sitemap_preload' ) || ! $this->options->get( 'manual_preload' ) ) {
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

		$this->sitemap_preload->run_preload( $sitemaps );
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

	/**
	 * Displays a notice if SimpleXML PHP extension is not enabled
	 *
	 * @since 3.2.5
	 * @author Remy Perona
	 * @return void
	 */
	public function simplexml_notice() {
		$screen = get_current_screen();

		// This filter is documented in inc/admin-bar.php.
		if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
			return;
		}

		if ( 'settings_page_wprocket' !== $screen->id ) {
			return;
		}

		if ( ! $this->options->get( 'sitemap_preload' ) ) {
			return;
		}

		if ( function_exists( 'simplexml_load_string' ) ) {
			return;
		}

		$message = sprintf(
			// Translators: %1$s = opening link tag, %2$s = closing link tag.
			__( '%1$sSimpleXML PHP extension%2$s is not enabled on your server. Please contact your host to enable it before running sitemap-based cache preloading.', 'rocket' ),
			'<a href="http://php.net/manual/en/book.simplexml.php" target="_blank" rel="noopener noreferrer">',
			'</a>'
		);

		\rocket_notice_html(
			[
				'status'  => 'warning',
				'message' => $message,
			]
		);
	}
}
