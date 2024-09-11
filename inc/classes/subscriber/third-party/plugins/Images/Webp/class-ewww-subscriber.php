<?php
namespace WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for the WebP support with EWWW.
 *
 * @since  3.4
 * @author Grégory Viguier
 */
class EWWW_Subscriber implements Webp_Interface, Subscriber_Interface {
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
	 * EWWW basename.
	 *
	 * @var    string
	 * @access private
	 * @author Grégory Viguier
	 */
	private $plugin_basename;

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
	 * {@inheritdoc}
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
		 * Every time EWWW is (de)activated, we must "sync" our webp cache option.
		 */
		if ( did_action( 'activate_' . $this->get_basename() ) ) {
			$this->plugin_activation();
		}
		if ( did_action( 'deactivate_' . $this->get_basename() ) ) {
			$this->plugin_deactivation();
		}
		add_action( 'activate_' . $this->get_basename(),   [ $this, 'plugin_activation' ], 20 );
		add_action( 'deactivate_' . $this->get_basename(), [ $this, 'plugin_deactivation' ], 20 );

		if ( ! function_exists( 'ewww_image_optimizer_get_option' ) ) {
			return;
		}

		/**
		 * Since Rocket already updates the config file after updating its options, there is no need to do it again if the CDN or zone options change.
		 * Sadly, we can’t monitor EWWW options accurately to update our config file.
		 */

		add_filter( 'rocket_cdn_cnames',       [ $this, 'maybe_remove_images_cnames' ], 1000, 2 );
		add_filter( 'rocket_allow_cdn_images', [ $this, 'maybe_remove_images_from_cdn_dropdown' ] );

		$option_names = [
			'ewww_image_optimizer_exactdn',
			'ewww_image_optimizer_webp_for_cdn',
		];

		foreach ( $option_names as $option_name ) {
			if ( $this->is_active_for_network() ) {
				add_filter( 'add_site_option_' . $option_name,    [ $this, 'trigger_webp_change' ] );
				add_filter( 'update_site_option_' . $option_name, [ $this, 'trigger_webp_change' ] );
				add_filter( 'delete_site_option_' . $option_name, [ $this, 'trigger_webp_change' ] );
			} else {
				add_filter( 'add_option_' . $option_name,    [ $this, 'trigger_webp_change' ] );
				add_filter( 'update_option_' . $option_name, [ $this, 'trigger_webp_change' ] );
				add_filter( 'delete_option_' . $option_name, [ $this, 'trigger_webp_change' ] );
			}
		}
	}

	/**
	 * Remove CDN hosts for images if EWWW uses ExactDN.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  array $hosts List of CDN URLs.
	 * @param  array $zones List of zones. Default is [ 'all' ].
	 * @return array
	 */
	public function maybe_remove_images_cnames( $hosts, $zones ) {
		if ( ! $hosts ) {
			return $hosts;
		}
		if ( ! ewww_image_optimizer_get_option( 'ewww_image_optimizer_exactdn' ) ) {
			return $hosts;
		}
		// EWWW uses ExactDN: WPR CDN should be disabled for images.
		if ( ! in_array( 'images', $zones, true ) ) {
			// Not asking for images.
			return $hosts;
		}
		if ( ! array_diff( $zones, [ 'all', 'images' ] ) ) {
			// This is clearly for images: return an empty list of hosts.
			return [];
		}

		// We also want other things, like js and css: let's only remove the hosts for 'images'.
		$cdn_urls = $this->options->get( 'cdn_cnames', [] );

		if ( ! $cdn_urls ) {
			return $hosts;
		}

		// Separate image hosts from the other ones.
		$image_hosts = [];
		$other_hosts = [];
		$cdn_zones   = $this->options->get( 'cdn_zone', [] );

		foreach ( $cdn_urls as $k => $urls ) {
			if ( ! in_array( $cdn_zones[ $k ], $zones, true ) ) {
				continue;
			}

			$urls = explode( ',', $urls );
			$urls = array_map( 'trim', $urls );

			if ( 'images' === $cdn_zones[ $k ] ) {
				foreach ( $urls as $url ) {
					$image_hosts[] = $url;
				}
			} else {
				foreach ( $urls as $url ) {
					$other_hosts[] = $url;
				}
			}
		}

		// Make sure the image hosts are not also used for other things (duplicate).
		$image_hosts = array_diff( $image_hosts, $other_hosts );

		// Then remove the remaining from the final list.
		return array_diff( $hosts, $image_hosts );
	}

	/**
	 * Maybe remove the images option from the CDN dropdown.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  bool $allow true to add the option, false otherwise.
	 * @return bool
	 */
	public function maybe_remove_images_from_cdn_dropdown( $allow ) {
		if ( ! $allow ) {
			return $allow;
		}
		if ( ! ewww_image_optimizer_get_option( 'ewww_image_optimizer_exactdn' ) ) {
			return $allow;
		}

		// EWWW uses ExactDN: WPR CDN should be disabled for images.
		return false;
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
		return 'EWWW';
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
		return 'ewww';
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
		if ( ! function_exists( 'ewww_image_optimizer_get_option' ) ) {
			// No EWWW, no webp.
			return false;
		}

		return (bool) ewww_image_optimizer_get_option( 'ewww_image_optimizer_webp' );
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
		if ( ! function_exists( 'ewww_image_optimizer_get_option' ) ) {
			// No EWWW, no webp.
			return false;
		}

		if ( ewww_image_optimizer_get_option( 'ewww_image_optimizer_exactdn' ) ) {
			// EWWW uses ExactDN (WPR CDN should be disabled for images).
			return true;
		}

		if ( ewww_image_optimizer_get_option( 'ewww_image_optimizer_webp_for_cdn' ) ) {
			// EWWW uses JS to rewrite file extensions.
			return true;
		}

		// Decide if rewrite rules are used.
		if ( ! function_exists( 'ewww_image_optimizer_webp_rewrite_verify' ) ) {
			// Uh?
			return false;
		}

		if ( ! function_exists( 'get_home_path' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		if ( ! function_exists( 'extract_from_markers' ) ) {
			require_once ABSPATH . 'wp-admin/includes/misc.php';
		}

		/**
		 * This function returns null if rules are present and valid. Otherwise it returns rules to be inserted.
		 * Note: this also returns null if WP Fastest Cache rules for webp are found in the file.
		 *
		 * @see ewww_image_optimizer_wpfc_webp_enabled()
		 */
		$use_rewrite_rules = ! ewww_image_optimizer_webp_rewrite_verify();

		/**
		 * Filter whether EWW is using rewrite rules for webp.
		 *
		 * @since  3.4
		 * @author Grégory Viguier
		 *
		 * @param bool $use_rewrite_rules True when EWWW uses rewrite rules. False otherwise.
		 */
		return (bool) apply_filters( 'rocket_webp_ewww_use_rewrite_rules', $use_rewrite_rules );
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
		if ( ! function_exists( 'ewww_image_optimizer_get_option' ) ) {
			// No EWWW, no webp.
			return false;
		}

		if ( ewww_image_optimizer_get_option( 'ewww_image_optimizer_exactdn' ) ) {
			// EWWW uses ExactDN.
			return true;
		}

		if ( ewww_image_optimizer_get_option( 'ewww_image_optimizer_webp_for_cdn' ) ) {
			// EWWW uses JS to rewrite file extensions.
			return true;
		}

		// At this point, the plugin is using rewrite rules or nothing.
		return false;
	}

	/**
	 * Get the plugin basename.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function get_basename() {
		if ( empty( $this->plugin_basename ) ) {
			$this->plugin_basename = rocket_has_constant( 'EWWW_IMAGE_OPTIMIZER_PLUGIN_FILE' )
				? plugin_basename( rocket_get_constant( 'EWWW_IMAGE_OPTIMIZER_PLUGIN_FILE' ) )
				: 'ewww-image-optimizer/ewww-image-optimizer.php';
		}

		return $this->plugin_basename;
	}

	/** ----------------------------------------------------------------------------------------- */
	/** PRIVATE TOOLS =========================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Tell if EWWW is active for network.
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

		if ( ! is_multisite() ) {
			$is = false;
			return $is;
		}

		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$is = is_plugin_active_for_network( $this->get_basename() ) && ! get_site_option( 'ewww_image_optimizer_allow_multisite_override' );

		return $is;
	}
}
