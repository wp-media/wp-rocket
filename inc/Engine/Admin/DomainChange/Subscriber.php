<?php

namespace WP_Rocket\Engine\Admin\DomainChange;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Filesystem_Direct;
use function Patchwork\Config\get;

class Subscriber implements Subscriber_Interface {

	/**
	 * Name of the option saving the last base URL.
	 *
	 * @string
	 */
	const LAST_BASE_URL_OPTION = 'wp_rocket_last_base_url';
	const LAST_OPTION_HASH     = 'wp_rocket_last_option_hash';

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return string[]
	 */
	public static function get_subscribed_events() {
		return [
			'admin_init'                    => 'maybe_launch_domain_changed',
			'rocket_configurations_changed' => 'configurations_changed',
			'rocket_domain_changed'         => 'maybe_clean_cache_domain_change',
			'update_option_' . rocket_get_constant( 'WP_ROCKET_SLUG' ) => 'save_hash_on_update_options',
		];
	}

	/**
	 * Maybe launch the domain changed event.
	 *
	 * @return void
	 */
	public function maybe_launch_domain_changed() {
		$base_url         = trailingslashit( home_url() );
		$base_url_encoded = base64_encode( $base_url ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode

		if ( ! get_option( self::LAST_BASE_URL_OPTION ) ) {
			update_option( self::LAST_BASE_URL_OPTION, $base_url_encoded );
			return;
		}

		$last_base_url_encoded = get_option( self::LAST_BASE_URL_OPTION );

		if ( $base_url_encoded === $last_base_url_encoded ) {
			return;
		}

		update_option( self::LAST_BASE_URL_OPTION, $base_url_encoded );

		$last_base_url = base64_decode( $last_base_url_encoded ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

		/**
		 * Fires when the domain of the website has been changed.
		 *
		 * @param string $current_url current URL from the website.
		 * @param string $old_url old URL from the website.
		 */
		do_action( 'rocket_domain_changed', $base_url, $last_base_url );
	}

	/**
	 * Check if wp rocket options have been changed.
	 *
	 * @return bool
	 */
	public function configurations_changed() {
		if ( ! get_option( self::LAST_OPTION_HASH ) || ! get_option( rocket_get_constant( 'WP_ROCKET_SLUG' ) ) ) {
			return true;
		}

		$last_option_hash = get_option( self::LAST_OPTION_HASH );

		$options = get_option( rocket_get_constant( 'WP_ROCKET_SLUG' ) );

		return $last_option_hash !== rocket_create_options_hash( $options );
	}

	/**
	 * Save the hash when options are saved.
	 *
	 * @param array $oldvalue old options.
	 * @param array $value new options.
	 * @return array|void
	 */
	public function save_hash_on_update_options( $oldvalue, $value ) {
		if ( ! is_array( $value ) ) {
			return;
		}

		$hash = rocket_create_options_hash( $value );

		update_option( self::LAST_OPTION_HASH, $hash );

		return $value;
	}

	/**
	 * Maybe clean cache on domain change.
	 *
	 * @return void
	 */
	public function maybe_clean_cache_domain_change() {
		if ( apply_filters( 'rocket_configurations_changed', false ) ) {
			return;
		}

		$options = get_option( rocket_get_constant( 'WP_ROCKET_SLUG' ) );

		if ( ! $options ) {
			return;
		}

		/**
		 * Fires after WP Rocket options that require a cache purge have changed
		 *
		 * @param array $value An array of submitted values for the settings.
		 */
		do_action( 'rocket_options_changed', $options );
	}
}
