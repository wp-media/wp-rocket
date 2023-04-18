<?php
namespace WP_Rocket\ThirdParty\Plugins;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\ReturnTypesTrait;

class WPGeotargeting implements Subscriber_Interface {

	use ReturnTypesTrait;

    /**
     * Returns an array of events that this subscriber wants to listen to.
     *
     * The array key is the event name. The value can be:
     *
     *  * The method name
     *  * An array with the method name and priority
     *  * An array with the method name, priority and number of accepted arguments
     *
     * For instance:
     *
     *  * array('hook_name' => 'method_name')
     *  * array('hook_name' => array('method_name', $priority))
     *  * array('hook_name' => array('method_name', $priority, $accepted_args))
     *  * array('hook_name' => array(array('method_name_1', $priority_1, $accepted_args_1)), array('method_name_2', $priority_2, $accepted_args_2)))
     *
     * @return array
     */
    public static function get_subscribed_events() {
		$events = [
			'geotWP/activated' => [ 'activate_geotargetingwp', 11 ],
			'geotWP/deactivated' => [ 'deactivate_geotargetingwp', 11 ],
		];

		if (! class_exists( 'GeotWP\GeotargetingWP' ) ) {
			return $events;
		}

		$events['rocket_htaccess_mod_rewrite'] = [ 'return_false', 72 ];
		$events['rocket_cache_dynamic_cookies'] = 'add_geot_cookies';
		$events['rocket_cache_mandatory_cookies'] = 'add_geot_cookies';
		$events['geot/pass_basic_rules'] = [ 'maybe_disable_rules', 10, 3 ];

		if ( ! get_option( 'geotWP-deactivated' ) ) {
			return $events;
		}

		// Update the WP Rocket rules on the .htaccess file.
		$events['admin_init'] = [
			[ 'flush_rocket_htaccess' ],
			[ 'rocket_generate_config_file' ],
		];

		delete_option( 'geotWP-deactivated' );

        return $events;
    }

	/**
	 * Disable rules on nowprocket parameter.
	 *
	 * @param bool $bool Is Disabled.
	 * @param array $opts Options.
	 * @param string $current_url Current URL.
	 * @return bool
	 */
	public function maybe_disable_rules( $bool, $opts, $current_url ) {
		if( str_contains( wp_parse_url( $current_url, PHP_URL_QUERY), 'nowprocket' ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Add cookies when we activate any goetargetingWP plugin.
	 *
	 * @author Damian Logghe
	 */
	public function activate_geotargetingwp() {
		add_filter( 'rocket_htaccess_mod_rewrite', [$this, 'return_false'], 72 );
		add_filter( 'rocket_cache_dynamic_cookies', [$this, 'add_geot_cookies'] );
		add_filter( 'rocket_cache_mandatory_cookies', [$this, 'add_geot_cookies'] );

		// Update the WP Rocket rules on the .htaccess file.
		flush_rocket_htaccess();

		// Regenerate the config file.
		rocket_generate_config_file();
	}

	/**
	 * Remove cookies when we deactivate the plugin.
	 *
	 * @author Damian Logghe
	 */
	public function deactivate_geotargetingwp() {
		// add into db a record saying we deactivated one of the family plugins.
		update_option( 'geotWP-deactivated', true );
		remove_filter( 'rocket_htaccess_mod_rewrite', [$this, 'return_false'], 72 );
		remove_filter( 'rocket_cache_dynamic_cookies', [$this, 'add_geot_cookies'] );
		remove_filter( 'rocket_cache_mandatory_cookies', [$this, 'add_geot_cookies'] );

		// Update the WP Rocket rules on the .htaccess file.
		flush_rocket_htaccess();

		// Regenerate the config file.
		rocket_generate_config_file();
	}

	/**
	 * Let users modify cache level by default set to country.
	 *
	 * @author Damian Logghe
	 *
	 * @param array $cookies An array of cookies.
	 * @return array Updated array of cookies
	 */
	public function add_geot_cookies( $cookies ) {
		// valid options are country, state, city.
		$enabled_cookies = apply_filters( 'rocket_geotargetingwp_enabled_cookies', [ 'country' ] );
		foreach ( $enabled_cookies as $enabled_cookie ) {
			if ( ! in_array( 'geot_rocket_' . $enabled_cookie, $cookies, true ) ) {
				$cookies[] = 'geot_rocket_' . $enabled_cookie;
			}
		}
		return $cookies;
	}
}
