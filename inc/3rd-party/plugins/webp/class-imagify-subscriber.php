<?php
namespace WP_Rocket\Subscriber\ThirdParty\Webp;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for the WebP support with Imagify.
 *
 * @since  3.4
 * @author Grégory Viguier
 */
class Imagify_Subscriber implements Subscriber_Interface {
	/**
	 * Options instance.
	 *
	 * @var    Options_Data
	 * @access private
	 * @author Remy Perona
	 */
	private $options;

	/**
	 * Imagify webp option value.
	 *
	 * @var    bool
	 * @access private
	 * @author Grégory Viguier
	 */
	private $plugin_display_webp;

	/**
	 * Constructor.
	 *
	 * @since  3.4
	 * @access public
	 * @author Remy Perona
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
			'wp_rocket_loaded' => 'load_hooks',
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

		if ( ! class_exists( '\Imagify_Options' ) || ! method_exists( '\Imagify_Options', 'get_instance' ) ) {
			return;
		}

		$instance = \Imagify_Options::get_instance();

		if ( ! method_exists( $instance, 'get_option_name' ) ) {
			return;
		}

		/**
		 * Every time Imagify is (de)activated, we must "sync" our webp cache option.
		 */
		add_action( 'imagify_activation',   [ $this, 'plugin_activation' ] );
		add_action( 'imagify_deactivation', [ $this, 'plugin_deactivation' ] );

		/**
		 * Every time Imagify’s option changes, we must "sync" our webp cache option.
		 */
		$option_name = $instance->get_option_name();

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
	 * Maybe deactivate webp cache on Imagify activation.
	 *
	 * @since  3.4
	 * @since  Imagify 1.9
	 * @access public
	 * @author Grégory Viguier
	 */
	public function plugin_activation() {
		if ( $this->options->get( 'cache_webp' ) && function_exists( 'get_imagify_option' ) && get_imagify_option( 'display_webp' ) ) {
			$this->trigger_webp_change( true );
		}
	}

	/**
	 * Maybe activate webp cache on Imagify activation.
	 *
	 * @since  3.4
	 * @since  Imagify 1.9
	 * @access public
	 * @author Grégory Viguier
	 */
	public function plugin_deactivation() {
		if ( $this->options->get( 'cache_webp' ) && function_exists( 'get_imagify_option' ) && get_imagify_option( 'display_webp' ) ) {
			$this->trigger_webp_change( false );
		}
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
			$this->trigger_webp_change( true );
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
		if ( get_current_network_id() !== $network_id ) {
			return;
		}
		$this->sync_on_option_update( $old_value, $value );
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
		if ( get_current_network_id() !== $network_id ) {
			return;
		}

		$this->plugin_display_webp = get_network_option( $network_id, $option, [] );
		$this->plugin_display_webp = ! empty( $this->plugin_display_webp['display_webp'] );
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
		if ( get_current_network_id() === $network_id && false !== $this->plugin_display_webp ) {
			$this->trigger_webp_change( false );
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
			$this->trigger_webp_change( true );
		}
	}

	/**
	 * Maybe activate or deactivate webp cache after Imagify option has been modified.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param mixed $old_value The old option value.
	 * @param mixed $value     The new option value.
	 */
	public function sync_on_option_update( $old_value, $value ) {
		$old_value = ! empty( $old_value['display_webp'] );
		$value     = ! empty( $value['display_webp'] );

		if ( $old_value !== $value ) {
			$this->trigger_webp_change( $value );
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
		$option_name = \Imagify_Options::get_instance()->get_option_name();

		if ( $option_name !== $option ) {
			return;
		}

		$this->plugin_display_webp = get_option( $option, [] );
		$this->plugin_display_webp = ! empty( $this->plugin_display_webp['display_webp'] );
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
		if ( false !== $this->plugin_display_webp ) {
			$this->trigger_webp_change( false );
		}
	}

	/** ----------------------------------------------------------------------------------------- */
	/** TOOLS =================================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Trigger an action when the webp feature is enabled/disabled in a third party plugin.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param bool $active True if the webp feature is now active in the third party plugin. False otherwise.
	 */
	private function trigger_webp_change( $active ) {
		/**
		 * Trigger an action when the webp feature is enabled/disabled in a third party plugin.
		 *
		 * @since  3.4
		 * @author Grégory Viguier
		 *
		 * @param bool $active True if the webp feature is now active in the third party plugin. False otherwise.
		 */
		do_action( 'rocket_third_party_webp_change', $active );
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

		if ( defined( 'IMAGIFY_FILE' ) ) {
			if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$is = is_multisite() && is_plugin_active_for_network( plugin_basename( IMAGIFY_FILE ) );
			return $is;
		}

		$is = is_multisite();
		return $is;
	}
}
