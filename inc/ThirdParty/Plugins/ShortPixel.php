<?php
namespace WP_Rocket\ThirdParty\Plugins;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\{Webp_Common, Webp_Interface};
use WP_Rocket\ThirdParty\ReturnTypesTrait;

class ShortPixel implements Webp_Interface, Subscriber_Interface {
	use Webp_Common;
	use ReturnTypesTrait;

	/**
	 * Options_Data instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * ShortPixel basename.
	 *
	 * @var string
	 */
	private $plugin_basename;

	/**
	 * ShortPixel’s "serve webp" option name.
	 *
	 * @var string
	 */
	private $plugin_option_name_to_serve_webp = 'wp-short-pixel-create-webp-markup';

	/**
	 * Temporarily store the result of $this->is_serving_webp().
	 *
	 * @var bool
	 */
	private $tmp_is_serving_webp;

	/**
	 * Constructor.
	 *
	 * @since 3.4
	 *
	 * @param Options_Data $options Options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.4
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		$events = [
			'rocket_webp_plugins' => 'register',
			'wp_rocket_loaded'    => 'load_hooks',
		];

		if ( rocket_has_constant( 'SHORTPIXEL_IMAGE_OPTIMISER_VERSION' ) ) {
			$events['wpmedia_plugin_family_show_imagify_banner'] = 'return_false';
		}

		return $events;
	}

	/**
	 * Launch filters.
	 *
	 * @since 3.4
	 */
	public function load_hooks() {
		if ( ! $this->options->get( 'cache_webp' ) ) {
			return;
		}

		/**
		 * Every time ShortPixel is (de)activated, we must "sync" our webp cache option.
		 */
		if ( did_action( 'activate_' . $this->get_basename() ) ) {
			$this->plugin_activation();
		}
		if ( did_action( 'deactivate_' . $this->get_basename() ) ) {
			$this->plugin_deactivation();
		}
		add_action( 'activate_' . $this->get_basename(),   [ $this, 'plugin_activation' ], 20 );
		add_action( 'deactivate_' . $this->get_basename(), [ $this, 'plugin_deactivation' ], 20 );

		if ( ! defined( 'SHORTPIXEL_IMAGE_OPTIMISER_VERSION' ) ) {
			return;
		}

		/**
		 * Since Rocket already updates the config file after updating its options, there is no need to do it again if the CDN or zone options change.
		 */

		/**
		 * Every time ShortPixel’s option changes, we must "sync" our webp cache option.
		 */
		$option_name = $this->plugin_option_name_to_serve_webp;

		add_filter( 'add_option_' . $option_name,    [ $this, 'sync_on_option_add' ], 10, 2 );
		add_filter( 'update_option_' . $option_name, [ $this, 'sync_on_option_update' ], 10, 2 );
		add_filter( 'delete_option',                 [ $this, 'store_option_value_before_delete' ] );
		add_filter( 'delete_option_' . $option_name, [ $this, 'sync_on_option_delete' ] );
	}

	/**
	 * Maybe deactivate webp cache after ShortPixel option has been successfully added.
	 *
	 * @since 3.4
	 *
	 * @param string $option Name of the option to add.
	 * @param mixed  $value  Value of the option.
	 */
	public function sync_on_option_add( $option, $value ) {
		if ( $value ) {
			$this->trigger_webp_change();
		}
	}

	/**
	 * Maybe activate or deactivate webp cache after ShortPixel option has been modified.
	 *
	 * @since 3.4
	 *
	 * @param mixed $old_value The old option value.
	 * @param mixed $value     The new option value.
	 */
	public function sync_on_option_update( $old_value, $value ) {
		/**
		 * 0 = Don’t serve webp.
		 * 1 = <picture> + buffer
		 * 2 = <picture> + hooks
		 * 3 = .htaccess
		 */
		$old_value = $old_value > 0;
		$value     = $value > 0;

		if ( $old_value !== $value ) {
			$this->trigger_webp_change();
		}
	}

	/**
	 * Store the ShortPixel option value before it is deleted.
	 *
	 * @since 3.4
	 *
	 * @param string $option Name of the option to delete.
	 */
	public function store_option_value_before_delete( $option ) {
		if ( $this->plugin_option_name_to_serve_webp === $option ) {
			$this->tmp_is_serving_webp = $this->is_serving_webp();
		}
	}

	/**
	 * Maybe activate webp cache after ShortPixel option has been deleted.
	 *
	 * @since 3.4
	 */
	public function sync_on_option_delete() {
		if ( false !== $this->tmp_is_serving_webp ) {
			$this->trigger_webp_change();
		}
	}

	/**
	 * Get the plugin name.
	 *
	 * @since 3.4
	 *
	 * @return string
	 */
	public function get_name() {
		return 'ShortPixel';
	}

	/**
	 * Get the plugin identifier.
	 *
	 * @since 3.4
	 *
	 * @return string
	 */
	public function get_id() {
		return 'shortpixel';
	}

	/**
	 * Tell if the plugin converts images to webp.
	 *
	 * @since 3.4
	 *
	 * @return bool
	 */
	public function is_converting_to_webp() {
		return (bool) get_option( 'wp-short-create-webp' ); // @phpstan-ignore-line
	}

	/**
	 * Tell if the plugin serves webp images on frontend.
	 *
	 * @since 3.4
	 *
	 * @return bool
	 */
	public function is_serving_webp() {
		return (bool) get_option( $this->plugin_option_name_to_serve_webp ); // @phpstan-ignore-line
	}

	/**
	 * Tell if the plugin uses a CDN-compatible technique to serve webp images on frontend.
	 *
	 * @since 3.4
	 *
	 * @return bool
	 */
	public function is_serving_webp_compatible_with_cdn() {
		$display = (int) get_option( $this->plugin_option_name_to_serve_webp ); // @phpstan-ignore-line

		if ( ! $display ) {
			// The option is not enabled, no webp.
			return false;
		}

		if ( 3 === $display ) {
			// The option is set to "rewrite rules".
			return false;
		}

		return true;
	}

	/**
	 * Get the plugin basename.
	 *
	 * @since 3.4
	 *
	 * @return string
	 */
	public function get_basename() {
		if ( empty( $this->plugin_basename ) ) {
			$this->plugin_basename = defined( 'SHORTPIXEL_PLUGIN_FILE' ) ? plugin_basename( SHORTPIXEL_PLUGIN_FILE ) : 'shortpixel-image-optimiser/wp-shortpixel.php';
		}

		return $this->plugin_basename;
	}
}
