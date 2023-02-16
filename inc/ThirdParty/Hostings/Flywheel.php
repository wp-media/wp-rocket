<?php

namespace WP_Rocket\ThirdParty\Hostings;

use WP_Rocket\ThirdParty\ReturnTypesTrait;

class Flywheel extends AbstractNoCacheHost
{
	use ReturnTypesTrait;

	public static function get_subscribed_events()
	{
		return [
		'rocket_varnish_field_settings' => 'varnish_field',
		'rocket_display_input_varnish_auto_purge' => 'return_false',
		'do_rocket_varnish_http_purge' => 'return_true',
		'do_rocket_generate_caching_files' => 'return_false',
		'rocket_cache_mandatory_cookies' => ['return_empty_array',PHP_INT_MAX],
		'rocket_varnish_ip' => 'ip_on_flywheel',
		'wp_rocket_loaded' => 'remove_partial_purge_hooks',
		];
	}

	/**
	 * Changes the text on the Varnish one-click block.
	 *
	 * @since  3.0
	 * @author Remy Perona
	 *
	 * @param array $settings Field settings data.
	 *
	 * @return array modified field settings data.
	 */
	public function varnish_field( $settings ) {
		$settings['varnish_auto_purge']['title'] = sprintf(
		// Translators: %s = Hosting name.
			__( 'Your site is hosted on %s, we have enabled Varnish auto-purge for compatibility.', 'rocket' ),
			'Flywheel'
		);

		return $settings;
	}

	/**
	 * Set up the right Varnish IP for Flywheel
	 *
	 * @since 2.6.8
	 * @param array $varnish_ip Varnish IP.
	 */
	function ip_on_flywheel( $varnish_ip ) {
		$varnish_ip[] = '127.0.0.1';

		return $varnish_ip;
	}

	/**
	 * Remove WP Rocket functions on WP core action hooks to prevent triggering a double cache clear.
	 *
	 * @since  3.3.1
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function remove_partial_purge_hooks() {
		// WP core action hooks rocket_clean_post() gets hooked into.
		$clean_post_hooks = [
			// Disables the refreshing of partial cache when content is edited.
			'wp_trash_post',
			'delete_post',
			'clean_post_cache',
			'wp_update_comment_count',
		];

		// Remove rocket_clean_post() from core action hooks.
		array_map(
			function( $hook ) {
				remove_action( $hook, 'rocket_clean_post' );
			},
			$clean_post_hooks
		);

		remove_filter( 'rocket_clean_files', 'rocket_clean_files_users' );
	}
}
