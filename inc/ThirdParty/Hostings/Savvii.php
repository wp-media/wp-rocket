<?php

namespace WP_Rocket\ThirdParty\Hostings;

use Savvii\CacheFlusherPlugin;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\ReturnTypesTrait;

/**
 * Compatibility for Savvii.
 *
 * @since 3.6.3
 */
class Savvii implements Subscriber_Interface {
	use ReturnTypesTrait;

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @see Subscriber_Interface.
	 *
	 * @since 3.6.3
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'do_rocket_generate_caching_files'        => [ 'return_false', PHP_INT_MAX ],
			'rocket_varnish_field_settings'           => 'varnish_addon_title',
			'rocket_display_input_varnish_auto_purge' => 'return_false',
			'rocket_cache_mandatory_cookies'          => 'return_empty_array',
			'init'                                    => 'clear_cache_after_savvii',
			'after_rocket_clean_domain'               => 'clean_savvii',
		];
	}

	/**
	 * Changes the text on the Varnish one-click block.
	 *
	 * @since 3.6.3 Rename and move to new architecture.
	 * @since  3.0
	 *
	 * @param array $settings Field settings data.
	 *
	 * @return array modified field settings data.
	 */
	public function varnish_addon_title( $settings ) {
		$settings['varnish_auto_purge']['title'] = sprintf(
		// Translators: %s = Hosting name.
			__( 'Your site is hosted on %s, we have enabled Varnish auto-purge for compatibility.', 'rocket' ),
			'Savvii'
		);

		return $settings;
	}

	/**
	 * Clear WP Rocket cache after purged the Varnish cache via Savvii Hosting.
	 *
	 * @since 3.6.3 Rename and move to new architecture. Refactor. Fix wrong nonce names/actions.
	 * @since 2.6.5
	 */
	public function clear_cache_after_savvii() {
		if (
			! (
				isset( $_REQUEST[ CacheFlusherPlugin::NAME_FLUSH_NOW ] )
				&&
				check_admin_referer( CacheFlusherPlugin::NAME_FLUSH_NOW )
			)
			&&
			! (
				isset( $_REQUEST[ CacheFlusherPlugin::NAME_DOMAINFLUSH_NOW ] )
				&&
				check_admin_referer( CacheFlusherPlugin::NAME_DOMAINFLUSH_NOW )
			)
		) {
			return;
		}

		if ( ! current_user_can( 'rocket_purge_cache' ) ) {
			return;
		}

		// Clear all caching files.
		rocket_clean_domain();

		// Preload cache.
		run_rocket_bot();
		run_rocket_sitemap_preload();
	}

	/**
	 * Call the cache server to purge the cache with Savvii hosting.
	 *
	 * @since 3.6.3 Rename and move to new architecture.
	 * @since 2.6.5
	 */
	public function clean_savvii() {
		do_action( 'warpdrive_domain_flush' ); //phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
	}
}
