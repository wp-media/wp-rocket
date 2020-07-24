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

		return '';
	}
}
