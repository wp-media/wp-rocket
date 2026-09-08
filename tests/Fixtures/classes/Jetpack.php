<?php
/**
 * Minimal global stub for the real Jetpack plugin's root-namespace `Jetpack` class.
 *
 * Used only by the issue #8789 slice 5 Easy-25 hook-collision scan
 * (tests/Integration/inc/ThirdParty/Plugins/PluginResolver/easy25HookCollisionScan.php)
 * to simulate Jetpack being present with its sitemaps module active, so
 * \WP_Rocket\ThirdParty\Plugins\Jetpack::get_subscribed_events() emits its
 * 'rocket_sitemap_preload_list' hook instead of bailing out early. Mirrors the
 * TRP_Translate_Press.php stub convention in this same directory.
 */

if ( ! class_exists( 'Jetpack' ) ) {
	class Jetpack {
		/**
		 * Always reports the queried module as active.
		 *
		 * @param string $module Module slug.
		 *
		 * @return bool
		 */
		public static function is_module_active( $module ) {
			return true;
		}
	}
}
