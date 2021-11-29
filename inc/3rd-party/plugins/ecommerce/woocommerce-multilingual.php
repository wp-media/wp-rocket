<?php

defined( 'ABSPATH' ) || exit;

// Compatibility with the currency switcher in WooCommerce Multilingual plugin.
// @since 3.9.0.3
// We are _temporarily_ disabling this filter, to be re-enabled after a future WCML release.
// that contains a better fix for the duplicate generation of cookies on this filter.
// @see #3998.
if ( defined( 'WCML_VERSION' ) ) :
	/**
	 * Use Cookie instead of WCSession
	 *
	 * @return string
	 */
	function rocket_wcml_use_cookie_storage() {
		return 'cookie';
	}
	/* add_filter( 'wcml_user_store_strategy', 'rocket_wcml_use_cookie_storage', 10, 2 ); */


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
	add_filter( 'rocket_cache_dynamic_cookies', 'rocket_wcml_add_dynamic_cookies' );

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
	add_filter( 'rocket_cache_mandatory_cookies', 'rocket_wcml_add_mandatory_cookies' );

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

		$check_key = function( $result, $key ) use ( $old_data, $data ) {
			$has_value_changed = function( $key ) use ( $old_data, $data ) {
				$get_value = function( $key, $data ) {
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
	add_action( 'updated_option', 'rocket_wcml_reset_settings', 10, 3 );

	/**
	 * Reset WP Rocket settings on WCML activation.
	 */
	function rocket_wcml_activate() {
		add_filter( 'rocket_htaccess_mod_rewrite', '__return_false', 64 );
		add_filter( 'rocket_cache_dynamic_cookies', 'rocket_wcml_add_dynamic_cookies' );
		add_filter( 'rocket_cache_mandatory_cookies', 'rocket_wcml_add_mandatory_cookies' );
		flush_rocket_htaccess();
		rocket_generate_config_file();
	}
	add_action( 'woocommerce-multilingual/wpml-woocommerce.php', 'rocket_wcml_activate', 11 );

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
	add_action( 'woocommerce-multilingual/wpml-woocommerce.php', 'rocket_wcml_deactivate', 11 );

	add_filter( 'rocket_htaccess_mod_rewrite', '__return_false', 64 );

endif;
