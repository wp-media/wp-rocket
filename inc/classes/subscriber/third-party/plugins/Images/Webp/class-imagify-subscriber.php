<?php
namespace WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for the WebP support with Imagify.
 *
 * @since  3.4
 * @author Grégory Viguier
 */
class Imagify_Subscriber implements Webp_Interface, Subscriber_Interface {
	use Webp_Common;

	/**
	 * Options_Data instance.
	 *
	 * @var    Options_Data
	 * @access private
	 * @author Remy Perona
	 */
	private $options;

	/**
	 * Imagify basename.
	 *
	 * @var    string
	 * @access private
	 * @author Grégory Viguier
	 */
	private $plugin_basename;

	/**
	 * Imagify’s "serve webp" option name.
	 *
	 * @var    string
	 * @access private
	 * @author Grégory Viguier
	 */
	private $plugin_option_name_to_serve_webp;

	/**
	 * Temporarily store the result of $this->is_serving_webp().
	 *
	 * @var    bool
	 * @access private
	 * @author Grégory Viguier
	 */
	private $tmp_is_serving_webp;

	/**
	 * Constructor.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param Options_Data $options Options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_webp_plugins' => 'register',
			'wp_rocket_loaded'    => 'load_hooks',
		];
	}

	/** ----------------------------------------------------------------------------------------- */
	/** HOOKS =================================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Launch filters.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 */
	public function load_hooks() {
		if ( ! $this->options->get( 'cache_webp' ) ) {
			return;
		}

		/**
		 * Every time Imagify is (de)activated, we must "sync" our webp cache option.
		 */
		add_action( 'imagify_activation',   [ $this, 'plugin_activation' ], 20 );
		add_action( 'imagify_deactivation', [ $this, 'plugin_deactivation' ], 20 );

		if ( ! rocket_has_constant( 'IMAGIFY_VERSION' ) ) {
			return;
		}

		/**
		 * Since Rocket already updates the config file after updating its options, there is no need to do it again if the CDN or zone options change.
		 */

		/**
		 * Every time Imagify’s option changes, we must "sync" our webp cache option.
		 */
		$option_name = $this->get_option_name_to_serve_webp();

		if ( $this->is_active_for_network() ) {
			add_filter( 'add_site_option_' . $option_name,        [ $this, 'sync_on_network_option_add' ], 10, 3 );
			add_filter( 'update_site_option_' . $option_name,     [ $this, 'sync_on_network_option_update' ], 10, 4 );
			add_filter( 'pre_delete_site_option_' . $option_name, [ $this, 'store_option_value_before_network_delete' ], 10, 2 );
			add_filter( 'delete_site_option_' . $option_name,     [ $this, 'sync_on_network_option_delete' ], 10, 2 );
			return;
		}

		add_filter( 'add_option_' . $option_name,    [ $this, 'sync_on_option_add' ], 10, 2 );
		add_filter( 'update_option_' . $option_name, [ $this, 'sync_on_option_update' ], 10, 2 );
		add_filter( 'delete_option',                 [ $this, 'store_option_value_before_delete' ] );
		add_filter( 'delete_option_' . $option_name, [ $this, 'sync_on_option_delete' ] );
	}

	/**
	 * Maybe deactivate webp cache after Imagify network option has been successfully added.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param string $option     Name of the network option.
	 * @param mixed  $value      Value of the network option.
	 * @param int    $network_id ID of the network.
	 */
	public function sync_on_network_option_add( $option, $value, $network_id ) {
		if ( get_current_network_id() === $network_id && ! empty( $value['display_webp'] ) ) {
			$this->trigger_webp_change();
		}
	}

	/**
	 * Maybe activate or deactivate webp cache after Imagify network option has been modified.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param string $option     Name of the network option.
	 * @param mixed  $value      Current value of the network option.
	 * @param mixed  $old_value  Old value of the network option.
	 * @param int    $network_id ID of the network.
	 */
	public function sync_on_network_option_update( $option, $value, $old_value, $network_id ) {
		if ( get_current_network_id() === $network_id ) {
			$this->sync_on_option_update( $old_value, $value );
		}
	}

	/**
	 * Store the Imagify network option value before it is deleted.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param string $option     Option name.
	 * @param int    $network_id ID of the network.
	 */
	public function store_option_value_before_network_delete( $option, $network_id ) {
		if ( get_current_network_id() === $network_id ) {
			$this->tmp_is_serving_webp = $this->is_serving_webp();
		}
	}

	/**
	 * Maybe activate webp cache after Imagify network option has been deleted.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param string $option     Name of the network option.
	 * @param int    $network_id ID of the network.
	 */
	public function sync_on_network_option_delete( $option, $network_id ) {
		if ( get_current_network_id() === $network_id && false !== $this->tmp_is_serving_webp ) {
			$this->trigger_webp_change();
		}
	}

	/**
	 * Maybe deactivate webp cache after Imagify option has been successfully added.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param string $option Name of the option to add.
	 * @param mixed  $value  Value of the option.
	 */
	public function sync_on_option_add( $option, $value ) {
		if ( ! empty( $value['display_webp'] ) ) {
			$this->trigger_webp_change();
		}
	}

	/**
	 * This function is used to synchronize the option update for Imagify plugin.
	 * It checks for the existence of certain keys in the old and new values of the options.
	 * Depending on the version of Imagify, the keys may differ.
	 * For Imagify version 2.2 and above, the keys are 'display_nextgen' and 'display_nextgen_method'.
	 * For Imagify version 2.1 and below, the keys are 'display_webp' and 'display_webp_method'.
	 * The function then checks if the old and new values of the display and method options have changed.
	 * If they have, it triggers a webp change.
	 *
	 * @param mixed $old_value The old option value.
	 * @param mixed $value     The new option value.
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 */
	public function sync_on_option_update( $old_value, $value ) {
		// Determine the key for the display option in the old and new values.
		$old_display_key = array_key_exists( 'display_nextgen', $old_value ) ? 'display_nextgen' : 'display_webp';
		$display_key     = array_key_exists( 'display_nextgen', $value ) ? 'display_nextgen' : 'display_webp';

		// Get the old and new values of the display option.
		$old_display = ! empty( $old_value[ $old_display_key ] );
		$display     = ! empty( $value[ $display_key ] );

		// Determine the key for the method option in the old and new values.
		$old_method_key = array_key_exists( 'display_nextgen_method', $old_value ) ? 'display_nextgen_method' : 'display_webp_method';
		$method_key     = array_key_exists( 'display_nextgen_method', $value ) ? 'display_nextgen_method' : 'display_webp_method';

		// If the old value of the method option is not set, set it to the corresponding display option.
		if ( ! isset( $old_value[ $old_method_key ] ) ) {
			$old_value[ $old_method_key ] = $old_value[ $old_display_key . '_method' ] ?? '';
		}

		// If the new value of the method option is not set, set it to the corresponding display option.
		if ( ! isset( $value[ $method_key ] ) ) {
			$value[ $method_key ] = $value[ $display_key . '_method' ] ?? '';
		}

		// If the old and new values of the display or method options have changed, trigger a webp change.
		if ( $old_display !== $display || $old_value[ $old_method_key ] !== $value[ $method_key ] ) {
			$this->trigger_webp_change();
		}
	}

	/**
	 * Store the Imagify option value before it is deleted.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param string $option Name of the option to delete.
	 */
	public function store_option_value_before_delete( $option ) {
		if ( $this->get_option_name_to_serve_webp() === $option ) {
			$this->tmp_is_serving_webp = $this->is_serving_webp();
		}
	}

	/**
	 * Maybe activate webp cache after Imagify option has been deleted.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param string $option Name of the deleted option.
	 */
	public function sync_on_option_delete( $option ) {
		if ( false !== $this->tmp_is_serving_webp ) {
			$this->trigger_webp_change();
		}
	}

	/** ----------------------------------------------------------------------------------------- */
	/** PUBLIC TOOLS ============================================================================ */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Get the plugin name.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return string
	 */
	public function get_name() {
		return 'Imagify';
	}

	/**
	 * Get the plugin identifier.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return string
	 */
	public function get_id() {
		return 'imagify';
	}

	/**
	 * Tell if the plugin converts images to webp.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function is_converting_to_webp() {
		if ( ! function_exists( 'get_imagify_option' ) ) {
			// No Imagify, no webp.
			return false;
		}

		return ( defined( 'IMAGIFY_VERSION' ) && version_compare( IMAGIFY_VERSION, '2.2', '>=' ) ) || get_imagify_option( 'convert_to_webp' );
	}

	/**
	 * Tell if the plugin serves webp images on frontend.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function is_serving_webp() {
		if ( ! function_exists( 'get_imagify_option' ) ) {
			// No Imagify, no webp.
			return false;
		}
		return get_imagify_option( 'display_webp' ) || get_imagify_option( 'display_nextgen' );
	}

	/**
	 * Tell if the plugin uses a CDN-compatible technique to serve webp images on frontend.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function is_serving_webp_compatible_with_cdn() {
		if ( ! $this->is_serving_webp() ) {
			return false;
		}

		return 'rewrite' !== get_imagify_option( 'display_webp_method' ) || 'rewrite' !== get_imagify_option( 'display_nextgen_method' );
	}

	/**
	 * Get the plugin basename.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return string
	 */
	public function get_basename(): string {
		if ( empty( $this->plugin_basename ) ) {
			$this->plugin_basename = rocket_has_constant( 'IMAGIFY_FILE' )
				? plugin_basename( rocket_get_constant( 'IMAGIFY_FILE' ) )
				: 'imagify/imagify.php';
		}

		return $this->plugin_basename;
	}

	/** ----------------------------------------------------------------------------------------- */
	/** PRIVATE TOOLS =========================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Get the name of the Imagify’s "serve webp" option.
	 *
	 * @since  3.4
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @return string
	 */
	private function get_option_name_to_serve_webp() {
		if ( ! empty( $this->plugin_option_name_to_serve_webp ) ) {
			return $this->plugin_option_name_to_serve_webp;
		}

		$default = 'imagify_settings';

		if ( ! class_exists( '\Imagify_Options' ) || ! method_exists( '\Imagify_Options', 'get_instance' ) ) {
			$this->plugin_option_name_to_serve_webp = $default;
			return $this->plugin_option_name_to_serve_webp;
		}

		$instance = \Imagify_Options::get_instance();

		if ( ! method_exists( $instance, 'get_option_name' ) ) {
			$this->plugin_option_name_to_serve_webp = $default;
			return $this->plugin_option_name_to_serve_webp;
		}

		$this->plugin_option_name_to_serve_webp = $instance->get_option_name();

		return $this->plugin_option_name_to_serve_webp;
	}

	/**
	 * Tell if Imagify is active for network.
	 *
	 * @since  3.4
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	private function is_active_for_network() {
		static $is;

		if ( isset( $is ) ) {
			return $is;
		}

		if ( function_exists( 'imagify_is_active_for_network' ) ) {
			$is = imagify_is_active_for_network();
			return $is;
		}

		if ( ! is_multisite() ) {
			$is = false;
			return $is;
		}

		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$is = is_plugin_active_for_network( $this->get_basename() );

		return $is;
	}
}
