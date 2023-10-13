<?php

namespace WP_Rocket\ThirdParty\Hostings;

/**
 * Host Resolver.
 *
 * @since 3.6.3
 */
class HostResolver {

	/**
	 * Name of the current host service.
	 *
	 * @var string
	 */
	private static $hostname = '';

	/**
	 * Get the name of an identifiable hosting service.
	 *
	 * @since 3.6.3
	 *
	 * @param bool $ignore_cached_hostname (optional) Don't use cached hostname when true.
	 *
	 * @return string Name of the hosting service or '' if no service is recognized.
	 */
	public static function get_host_service( $ignore_cached_hostname = false ) {

		if ( ! $ignore_cached_hostname && ! empty( self::$hostname ) ) {
			return self::$hostname;
		}

		if ( isset( $_SERVER['GROUPONE_BRAND_NAME'] ) ) {
			$group_one_brand_name = strtolower( sanitize_text_field( wp_unslash( $_SERVER['GROUPONE_BRAND_NAME'] ) ) );

			switch ( $group_one_brand_name ) {
				case 'one.com':
					self::$hostname = 'onecom';
					return 'onecom';
				case 'proisp.no':
					self::$hostname = 'proisp';
					return 'proisp';
			}
		}

		if ( isset( $_SERVER['cw_allowed_ip'] ) ) {
			self::$hostname = 'cloudways';

			return 'cloudways';
		}

		if ( rocket_get_constant( 'IS_PRESSABLE' ) ) {
			self::$hostname = 'pressable';

			return 'pressable';
		}

		if ( getenv( 'SPINUPWP_CACHE_PATH' ) ) {
			self::$hostname = 'spinupwp';

			return 'spinupwp';
		}

		if (
		(
			class_exists( 'WpeCommon' )
			&&
			function_exists( 'wpe_param' )
		)
		) {
			self::$hostname = 'wpengine';

			return 'wpengine';
		}

		if ( rocket_has_constant( 'O2SWITCH_VARNISH_PURGE_KEY' ) ) {
			self::$hostname = 'o2switch';

			return 'o2switch';
		}

		if ( rocket_get_constant( 'WPCOMSH_VERSION' ) ) {
			self::$hostname = 'wordpresscom';

			return 'wordpresscom';
		}

		if (
			rocket_get_constant( '\Savvii\CacheFlusherPlugin::NAME_FLUSH_NOW' )
			&&
			rocket_get_constant( '\Savvii\CacheFlusherPlugin::NAME_DOMAINFLUSH_NOW' )
		) {
			return 'savvii';
		}

		if ( self::is_dreampress() ) {
			return 'dreampress';
		}

		if ( isset( $_SERVER['HTTP_WPXCLOUD'] ) ) {
			self::$hostname = 'wpxcloud';
			return 'wpxcloud';
		}

		if ( isset( $_SERVER['X-LSCACHE'] ) ) {
			self::$hostname = 'litespeed';
			return 'litespeed';
		}

		if ( class_exists( '\WPaas\Plugin' ) ) {
			self::$hostname = 'godaddy';
			return 'godaddy';
		}

		if ( isset( $_SERVER['KINSTA_CACHE_ZONE'] ) ) {
			self::$hostname = 'kinsta';
			return 'kinsta';
		}

		if ( defined( 'WP_NINUKIS_WP_NAME' ) || class_exists( 'NinukisCaching' ) ) {
			self::$hostname = 'pressidium';
			return self::$hostname;
		}

		return '';
	}

	/**
	 * Checks if the current host is DreamPress
	 *
	 * @since 3.7.2
	 *
	 * @return boolean
	 */
	private static function is_dreampress() {
		if ( ! isset( $_SERVER['DH_USER'] ) ) {
			return false;
		}

		if (
			! rocket_get_constant( 'WP_ROCKET_IS_TESTING', false )
			&&
			'dp-' !== substr( gethostname(), 0, 3 )
		) {
			return false;
		}

		return 'wp_' === substr( sanitize_key( wp_unslash( $_SERVER['DH_USER'] ) ), 0, 3 );
	}
}
