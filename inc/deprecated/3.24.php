<?php

use WP_Rocket\ThirdParty\Hostings\SiteGround;

// Same condition the removed inc/3rd-party/hosting/siteground.php was loaded under, so function_exists() checks keep their meaning.
if ( rocket_is_plugin_active( 'sg-cachepress/sg-cachepress.php' ) ) {
	/**
	 * Returns the current version of the SG Optimizer plugin.
	 *
	 * @since 3.2.3.1
	 * @deprecated 3.24
	 *
	 * @return string version number.
	 */
	function rocket_get_sg_optimizer_version() {
		_deprecated_function( __FUNCTION__, '3.24', 'WP_Rocket\ThirdParty\Hostings\SiteGround::get_sg_optimizer_version()' );

		return ( new SiteGround() )->get_sg_optimizer_version();
	}

	/**
	 * Checks if SG Optimizer Supercache is active.
	 *
	 * @since 3.2.3.1
	 * @deprecated 3.24
	 *
	 * @return bool
	 */
	function rocket_is_supercacher_active() {
		_deprecated_function( __FUNCTION__, '3.24', 'WP_Rocket\ThirdParty\Hostings\SiteGround::is_supercacher_active()' );

		return ( new SiteGround() )->is_supercacher_active();
	}

	/**
	 * Call the cache server to purge the cache with SuperCacher (SiteGround).
	 *
	 * @since 2.3
	 * @deprecated 3.24
	 *
	 * @return void
	 */
	function rocket_clean_supercacher() {
		_deprecated_function( __FUNCTION__, '3.24', 'WP_Rocket\ThirdParty\Hostings\SiteGround::clean_supercacher()' );

		( new SiteGround() )->clean_supercacher();
	}

	/**
	 * Clean WP Rocket cache when cleaning SG cache.
	 *
	 * @since 2.3
	 * @deprecated 3.24
	 *
	 * @return void
	 */
	function rocket_sg_clear_cache() {
		_deprecated_function( __FUNCTION__, '3.24', 'WP_Rocket\ThirdParty\Hostings\SiteGround::sg_clear_cache()' );

		( new SiteGround() )->sg_clear_cache();
	}
}
