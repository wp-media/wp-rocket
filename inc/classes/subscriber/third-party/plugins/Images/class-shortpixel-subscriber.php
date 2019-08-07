<?php
namespace WP_Rocket\Subscriber\Third_Party\Plugins\Images;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for the WebP support with ShortPixel.
 *
 * @since  3.4
 * @author Grégory Viguier
 */
class ShortPixel_Subscriber implements Subscriber_Interface {
	/**
	 * Options instance.
	 *
	 * @var    Options_Data
	 * @access private
	 * @author Remy Perona
	 */
	private $options;

	/**
	 * ShortPixel’s option name.
	 *
	 * @var    string
	 * @access private
	 * @author Grégory Viguier
	 */
	private $option_name = 'wp-short-pixel-create-webp-markup';

	/**
	 * ShortPixel webp option value.
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

		/**
		 * Every time ShortPixel is (de)activated, we must "sync" our webp cache option.
		 */
		$file = defined( 'SHORTPIXEL_PLUGIN_FILE' ) ? plugin_basename( SHORTPIXEL_PLUGIN_FILE ) : 'shortpixel-image-optimiser/wp-shortpixel.php';

		add_action( 'activate_' . $file,   [ $this, 'plugin_activation' ] );
		add_action( 'deactivate_' . $file, [ $this, 'plugin_deactivation' ] );

		/**
		 * Every time ShortPixel’s option changes, we must "sync" our webp cache option.
		 */
		add_filter( 'add_option_' . $this->option_name,    [ $this, 'sync_on_option_add' ], 10, 2 );
		add_filter( 'update_option_' . $this->option_name, [ $this, 'sync_on_option_update' ], 10, 2 );
		add_filter( 'delete_option',                       [ $this, 'store_option_value_before_delete' ] );
		add_filter( 'delete_option_' . $this->option_name, [ $this, 'sync_on_option_delete' ] );
	}

	/**
	 * Maybe deactivate webp cache on ShortPixel activation.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 */
	public function plugin_activation() {
		if ( get_option( $this->option_name ) ) {
			$this->trigger_webp_change( true );
		}
	}

	/**
	 * Maybe activate webp cache on ShortPixel activation.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 */
	public function plugin_deactivation() {
		if ( get_option( $this->option_name ) ) {
			$this->trigger_webp_change( false );
		}
	}

	/**
	 * Maybe deactivate webp cache after ShortPixel option has been successfully added.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param string $option Name of the option to add.
	 * @param mixed  $value  Value of the option.
	 */
	public function sync_on_option_add( $option, $value ) {
		$this->trigger_webp_change( true );
	}

	/**
	 * Maybe activate or deactivate webp cache after ShortPixel option has been modified.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param mixed $old_value The old option value.
	 * @param mixed $value     The new option value.
	 */
	public function sync_on_option_update( $old_value, $value ) {
		$old_value = ! empty( $old_value );
		$value     = ! empty( $value );

		if ( $old_value !== $value ) {
			$this->trigger_webp_change( $value );
		}
	}

	/**
	 * Store the ShortPixel option value before it is deleted.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param string $option Name of the option to delete.
	 */
	public function store_option_value_before_delete( $option ) {
		if ( $this->option_name === $option ) {
			$this->plugin_display_webp = (bool) get_option( $this->option_name );
		}
	}

	/**
	 * Maybe activate webp cache after ShortPixel option has been deleted.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param string $option Name of the deleted option.
	 */
	public function sync_on_option_delete( $option ) {
		if ( $this->plugin_display_webp ) {
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
}
