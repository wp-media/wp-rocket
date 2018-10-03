<?php
namespace WP_Rocket\Subscriber\Third_Party\Plugins;

use WP_Rocket\Event_Management\Subscriber_Interface;

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Class that handles events related to plugins that add mobile themes.
 *
 * @since  3.2
 * @author Grégory Viguier
 */
class Mobile_Subscriber implements Subscriber_Interface {

	/**
	 * Options to activate when a mobile plugin is active.
	 *
	 * @since  3.2
	 * @access protected
	 * @author Grégory Viguier
	 *
	 * @var array
	 */
	protected static $options = [
		'cache_mobile'            => 1,
		'do_caching_mobile_files' => 1,
	];

	/**
	 * Cache the value of self::is_mobile_plugin_active().
	 *
	 * @since  3.2
	 * @access protected
	 * @author Grégory Viguier
	 *
	 * @var array An array of arrays of booleans.
	 *            First level of keys corresponds to the network ID. Second level of keys corresponds to the blog ID.
	 */
	protected static $is_mobile_active = [];

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		// In case a mobile plugin has already been activated.
		$do   = [];
		$undo = [];

		foreach ( static::get_mobile_plugins() as $plugin ) {
			if ( \did_action( 'activate_' . $plugin ) ) {
				$do[] = $plugin;
			}
			if ( \did_action( 'deactivate_' . $plugin ) ) {
				$undo[] = $plugin;
			}
		}

		if ( array_diff( $do, $undo ) || array_diff( $undo, $do ) ) {
			static::update_mobile_cache_activation();
		}

		// Register events.
		$events = [
			// Plugin activation/deactivation.
			'add_option_active_plugins'                  => [ 'add_option_callback', 10, 2 ],
			'update_option_active_plugins'               => [ 'update_option_callback', 10, 2 ],
			'delete_option_active_plugins'               => 'delete_option_callback',
			'add_site_option_active_sitewide_plugins'    => [ 'add_site_option_callback', 10, 3 ],
			'update_site_option_active_sitewide_plugins' => [ 'update_site_option_callback', 10, 4 ],
			'delete_site_option_active_sitewide_plugins' => [ 'delete_site_option_callback', 10, 2 ],
			// WPR settings (`get_option()`).
			'option_' . WP_ROCKET_SLUG                   => 'mobile_options_filter',
		];

		foreach ( static::$options as $option => $value ) {
			// WPR settings (`get_rocket_option()`).
			$events[ 'pre_get_rocket_option_' . $option ] = 'is_mobile_plugin_active_callback';
		}

		return $events;
	}

	/** ----------------------------------------------------------------------------------------- */
	/** HOOK CALLBACKS ========================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Callback triggered after the option `active_plugins` is created.
	 * This should normally never be triggered.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param string $option Name of the option to add.
	 * @param mixed  $value  Value of the option.
	 */
	public function add_option_callback( $option, $value ) {
		$this->maybe_update_mobile_cache_activation( $value, [] );
	}

	/**
	 * Callback triggered after the option `active_plugins` is updated.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param mixed $old_value The old option value.
	 * @param mixed $value     Value of the option.
	 */
	public function update_option_callback( $old_value, $value ) {
		$this->maybe_update_mobile_cache_activation( $value, $old_value );
	}

	/**
	 * Callback triggered after the option `active_plugins` is deleted.
	 * Very low probability to be triggered.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 */
	public function delete_option_callback() {
		static::update_mobile_cache_activation();
	}

	/**
	 * Callback triggered after the option `active_sitewide_plugins` is created.
	 * This should normally never be triggered.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param string $option     Name of the option to add.
	 * @param mixed  $value      Value of the option.
	 * @param int    $network_id ID of the network.
	 */
	public function add_site_option_callback( $option, $value, $network_id ) {
		if ( get_current_network_id() === $network_id ) {
			$this->maybe_update_mobile_cache_activation( $value, [] );
		}
	}

	/**
	 * Callback triggered after the option `active_sitewide_plugins` is updated.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param string $option     Name of the option to add.
	 * @param mixed  $value      Value of the option.
	 * @param mixed  $old_value  The old option value.
	 * @param int    $network_id ID of the network.
	 */
	public function update_site_option_callback( $option, $value, $old_value, $network_id ) {
		if ( get_current_network_id() === $network_id ) {
			$this->maybe_update_mobile_cache_activation( $value, $old_value );
		}
	}

	/**
	 * Callback triggered after the option `active_sitewide_plugins` is deleted.
	 * Very low probability to be triggered.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param string $option     Name of the option to add.
	 * @param int    $network_id ID of the network.
	 */
	public function delete_site_option_callback( $option, $network_id ) {
		if ( get_current_network_id() === $network_id ) {
			static::update_mobile_cache_activation();
		}
	}

	/**
	 * Enable mobile caching when a mobile plugin is activated, or revert it back to its previous state when a mobile plugin is deactivated.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param mixed $value     The new option value.
	 * @param mixed $old_value The old option value.
	 */
	public function maybe_update_mobile_cache_activation( $value, $old_value ) {
		$plugins   = static::get_mobile_plugins();
		$value     = array_intersect( $plugins, (array) $value );
		$old_value = array_intersect( $plugins, (array) $old_value );

		if ( $value !== $old_value ) {
			static::update_mobile_cache_activation();
		}
	}

	/**
	 * Forces the values for the mobile options if a mobile plugin is active.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  array $values Option values.
	 * @return array
	 */
	public function mobile_options_filter( $values ) {
		if ( static::is_mobile_plugin_active() ) {
			return array_merge( (array) $values, static::$options );
		}

		return $values;
	}

	/**
	 * Forces the value for a mobile option if a mobile plugin is active.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  int|null $value Option value.
	 * @return int|null
	 */
	public function is_mobile_plugin_active_callback( $value ) {
		if ( static::is_mobile_plugin_active() ) {
			return 1;
		}

		return $value;
	}

	/** ----------------------------------------------------------------------------------------- */
	/** MAIN HELPERS ============================================================================ */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Update the config file and the advanced cache file.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 */
	public static function update_mobile_cache_activation() {
		// Reset class cache.
		static::reset_class_cache();

		// Update the config file.
		rocket_generate_config_file();
		// Update the advanced cache file.
		rocket_generate_advanced_cache_file();
	}

	/**
	 * Reset `is_mobile_plugin_active()` cache.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 */
	public static function reset_class_cache() {
		// Reset class cache.
		unset( static::$is_mobile_active[ get_current_network_id() ][ get_current_blog_id() ] );
	}

	/**
	 * Get the concerned plugins.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return array
	 */
	public static function get_mobile_plugins() {
		return [
			'wptouch/wptouch.php',
			'wiziapp-create-your-own-native-iphone-app/wiziapp.php',
			'wordpress-mobile-pack/wordpress-mobile-pack.php',
			'wp-mobilizer/wp-mobilizer.php',
			'wp-mobile-edition/wp-mobile-edition.php',
			'device-theme-switcher/dts_controller.php',
			'wp-mobile-detect/wp-mobile-detect.php',
			'easy-social-share-buttons3/easy-social-share-buttons3.php',
		];
	}

	/**
	 * Tell if a mobile plugin is active.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return bool True if a mobile plugin in the list is active, false otherwise.
	 */
	public static function is_mobile_plugin_active() {
		$network_id = get_current_network_id();
		$blog_id    = get_current_blog_id();

		if ( isset( static::$is_mobile_active[ $network_id ][ $blog_id ] ) ) {
			return static::$is_mobile_active[ $network_id ][ $blog_id ];
		}

		if ( ! function_exists( '\is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( ! isset( static::$is_mobile_active[ $network_id ] ) ) {
			static::$is_mobile_active[ $network_id ] = [];
		}

		foreach ( static::get_mobile_plugins() as $mobile_plugin ) {
			if ( \is_plugin_active( $mobile_plugin ) ) {
				static::$is_mobile_active[ $network_id ][ $blog_id ] = true;
				return true;
			}
		}

		static::$is_mobile_active[ $network_id ][ $blog_id ] = false;
		return false;
	}
}
