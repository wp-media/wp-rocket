<?php

defined( 'ABSPATH' ) || exit;

/**
 * Check if WCML is active and has minimum requirements.
 *
 * @return bool
 */
function rocket_wcml_has_requirements() {
	return defined( 'WCML_VERSION' )
		&& version_compare( WCML_VERSION, '4.12.6', '>=' );
}

if ( rocket_wcml_has_requirements() ) :
	/**
	 * Use Cookie instead of WCSession
	 *
	 * @return string
	 */
	function rocket_wcml_use_cookie_storage() {
		return 'cookie';
	}
	add_filter( 'wcml_user_store_strategy', 'rocket_wcml_use_cookie_storage', 10, 2 );

	add_filter( 'rocket_cache_dynamic_cookies', 'rocket_wcml_add_dynamic_cookies' );
	add_filter( 'rocket_cache_mandatory_cookies', 'rocket_wcml_add_mandatory_cookies' );
	add_action( 'updated_option', 'rocket_wcml_reset_settings', 10, 3 );

	/**
	 * Reset WP Rocket settings on WCML deactivation.
	 */
	function rocket_wcml_deactivate() {
		remove_filter( 'rocket_htaccess_mod_rewrite', '__return_false', 64 );
		remove_filter( 'rocket_cache_dynamic_cookies', 'rocket_wcml_add_dynamic_cookies' );
		remove_filter( 'rocket_cache_mandatory_cookies', 'rocket_wcml_add_mandatory_cookies' );
		flush_rocket_htaccess();
		rocket_generate_config_file();
	}
	add_action( 'deactivate_woocommerce-multilingual/wpml-woocommerce.php', 'rocket_wcml_deactivate', 11 );

	add_filter( 'rocket_htaccess_mod_rewrite', '__return_false', 64 );

endif;

/**
 * Add dynamic cookies for WCML.
 *
 * @param array $cookies Cookies.
 *
 * @return array
 */
function rocket_wcml_add_dynamic_cookies( $cookies ) {
	$cookies[] = 'wcml_client_currency';
	$cookies[] = 'wcml_client_currency_language';
	$cookies[] = 'wcml_client_country';

	return $cookies;
}

/**
 * Add mandatory cookies for WCML.
 *
 * @param array $cookies Cookies.
 *
 * @return array
 */
function rocket_wcml_add_mandatory_cookies( $cookies ) {
	// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
	if ( apply_filters( 'wcml_geolocation_is_used', false ) ) {
		// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		$cookies[] = 'wcml_client_country';
	}

	return $cookies;
}

/**
 * Reset WP Rocket settings when a relevant WCML setting is changed.
 *
 * @param string $option   Option name.
 * @param mixed  $old_data Old data.
 * @param mixed  $data     New data.
 */
function rocket_wcml_reset_settings( $option, $old_data, $data ) {
	$keys_to_check = [
		'enable_multi_currency',
		'currency_mode',
		'default_currencies',
	];

	$check_key = function ( $result, $key ) use ( $old_data, $data ) {
		$has_value_changed = function ( $key ) use ( $old_data, $data ) {
			$get_value = function ( $key, $data ) {
				return isset( $data[ $key ] ) ? $data[ $key ] : null;
			};

			return $get_value( $key, $old_data ) !== $get_value( $key, $data );
		};

		return $result || $has_value_changed( $key );
	};

	if (
		'_wcml_settings' === $option
		&& array_reduce( $keys_to_check, $check_key, false )
	) {
		flush_rocket_htaccess();
		rocket_generate_config_file();
	}
}

/**
 * Reset WP Rocket settings on WCML activation.
 */
function rocket_wcml_activate() {
	if ( rocket_wcml_has_requirements() ) {
		add_filter( 'rocket_htaccess_mod_rewrite', '__return_false', 64 );
		add_filter( 'rocket_cache_dynamic_cookies', 'rocket_wcml_add_dynamic_cookies' );
		add_filter( 'rocket_cache_mandatory_cookies', 'rocket_wcml_add_mandatory_cookies' );
		flush_rocket_htaccess();
		rocket_generate_config_file();
	}
}
add_action( 'activate_woocommerce-multilingual/wpml-woocommerce.php', 'rocket_wcml_activate', 11 );
