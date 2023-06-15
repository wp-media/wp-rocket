<?php

namespace WP_Rocket\Engine\Admin\DomainChange;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Filesystem_Direct;

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
			'update_option_' . rocket_get_constant( 'WP_ROCKET_SLUG' ) => [ 'save_hash_on_update_options', 10, 2 ],
			'rocket_notice_args' => 'add_regenerate_configuration_action',
			'admin_post_rocket_regenerate_configuration' => 'regenerate_configuration',
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
		return rocket_create_options_hash( $options ) !== $last_option_hash;
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
	}

	/**
	 * Maybe clean cache on domain change.
	 *
	 * @return void
	 */
	public function maybe_clean_cache_domain_change() {
		/**
		 * Has Rocket configurations changed.
		 *
		 * @param bool $changed Has Rocket configurations changed.
		 * @return bool
		 */
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

	public function maybe_display_domain_change_notice() {
		$notice = get_transient('rocket_domain_changed');

		if( ! $notice ) {
			return;
		}

		rocket_notice_html([
			'status' => '',
			'message' => esc_html__( 'We detected that the website domain has changed. The configuration files must be regenerated for the page cache and all other optimizations to work as intended. Learn More (https://docs.wp-rocket.me/article/705-changing-domains-migrating-sites-with-wp-rocket)', 'rocket' ),
		]);
	}

	public function add_regenerate_configuration_action($args) {
		if(! key_exists('action', $args) || 'regenerate_configuration' !== $args['action']) {
			return $args;
		}

		$params         = [
			'action' => 'rocket_regenerate_configuration',
		];

		$args['action'] = '<a class="wp-core-ui button" href="' . add_query_arg( $params, wp_nonce_url( admin_url( 'admin-post.php' ), 'rocket_regenerate_configuration' ) ) . '">' . __( 'Regenerate WP Rocket configuration files now', 'rocket' ) . '</a>';

		return $args;
	}

	public function regenerate_configuration() {
		check_admin_referer( 'rocket_regenerate_configuration' );

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		rocket_generate_advanced_cache_file();
		flush_rocket_htaccess();

		wp_safe_redirect( wp_get_referer() );
		rocket_get_constant( 'WP_ROCKET_IS_TESTING', false ) ? wp_die() : exit;
	}
}
