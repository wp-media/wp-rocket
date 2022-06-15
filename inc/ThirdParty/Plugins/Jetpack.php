<?php

namespace WP_Rocket\ThirdParty\Plugins;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\ReturnTypesTrait;

class Jetpack implements Subscriber_Interface {

	use ReturnTypesTrait;

	/**
	 * Option instance.
	 *
	 * @var Options_Data
	 */
	protected $option;

	/**
	 * Instantiate class.
	 *
	 * @param Options_Data $option Option instance.
	 */
	public function __construct( Options_Data $option ) {
		$this->option = $option;
	}

	/**
	 * Subscribed events.
	 */
	public static function get_subscribed_events() {
		$events = [
			'deactivate_jetpack/jetpack.php' => [ 'remove_jetpack_cookie_law_mandatory_cookie', 11 ],
		];

		if ( ! class_exists( 'Jetpack' ) ) {
			return $events;
		}

		if ( \Jetpack::is_module_active( 'sitemaps' ) && function_exists( 'jetpack_sitemap_uri' ) ) {
			$events['rocket_sitemap_preload_list'] = 'add_jetpack_sitemap';
		}

		if ( \Jetpack::is_module_active( 'widgets' ) ) {
			$events['rocket_cache_mandatory_cookies'] = 'add_jetpack_cookie_law_mandatory_cookie';
			$events['rocket_htaccess_mod_rewrite']    = [ 'return_false', 76 ];
			$events['admin_init']                     = 'activate_jetpack_cookie_law';
		}

		return $events;
	}


	/**
	 * Remove cookies if Jetpack gets deactivated.
	 */
	public function remove_jetpack_cookie_law_mandatory_cookie() {
		remove_filter( 'rocket_htaccess_mod_rewrite', '__return_false', 76 );
		remove_filter( 'rocket_cache_mandatory_cookies', '_rocket_add_eu_cookie_law_mandatory_cookie' );

		// Update the WP Rocket rules on the .htaccess file.
		flush_rocket_htaccess();

		// Regenerate the config file.
		rocket_generate_config_file();

		// Delete our option.
		delete_option( 'rocket_jetpack_eu_cookie_widget' );
	}

	/**
	 * Add Jetpack sitemap to preload list
	 *
	 * @param Array $sitemaps Array of sitemaps to preload.
	 * @return Array Updated Array of sitemaps to preload
	 */
	public function add_jetpack_sitemap( $sitemaps ) {
		$sitemaps['jetpack'] = jetpack_sitemap_uri();

		return $sitemaps;
	}

	/**
	 * Add the EU Cookie Law to the list of mandatory cookies before generating caching files.
	 *
	 * @param array $cookies List of mandatory cookies.
	 */
	public function add_jetpack_cookie_law_mandatory_cookie( $cookies ) {
		$cookies['jetpack-eu-cookie-law'] = 'eucookielaw';

		return $cookies;
	}

	/**
	 * Add Jetpack cookie when:
	 *  - Jetpack is active.
	 *  - Jetpack's Extra Sidebar Widgets module is active.
	 *  - The widget is active.
	 *  - the rocket_jetpack_eu_cookie_widget option is empty or not set.
	 */
	public function activate_jetpack_cookie_law() {
		$rocket_jp_eu_cookie_widget = $this->option->get( 'rocket_jetpack_eu_cookie_widget' );

		if (
			is_active_widget( false, false, 'eu_cookie_law_widget' )
			&& empty( $rocket_jp_eu_cookie_widget )
		) {
			add_filter( 'rocket_htaccess_mod_rewrite', '__return_false', 76 );
			add_filter( 'rocket_cache_mandatory_cookies',  [ __CLASS__, 'add_jetpack_cookie_law_mandatory_cookie' ] );

			// Update the WP Rocket rules on the .htaccess file.
			flush_rocket_htaccess();

			// Regenerate the config file.
			rocket_generate_config_file();

			// Set the option, so this is not triggered again.
			update_option( 'rocket_jetpack_eu_cookie_widget', 1, true );
		}
	}
}
