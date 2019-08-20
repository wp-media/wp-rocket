<?php
namespace WP_Rocket\Subscriber\Media;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Subscriber\CDN\CDNSubscriber;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Webp_Interface;

/**
 * Subscriber for the WebP support.
 *
 * @since  3.4
 * @author Remy Perona
 * @author Grégory Viguier
 */
class Webp_Subscriber implements Subscriber_Interface {
	/**
	 * Options instance.
	 *
	 * @var    Options_Data
	 * @access private
	 * @author Remy Perona
	 */
	private $options;

	/**
	 * CDNSubscriber instance.
	 *
	 * @var    CDNSubscriber
	 * @access private
	 * @author Grégory Viguier
	 */
	private $cdn_subscriber;

	/**
	 * \WP_Filesystem_Direct instance.
	 *
	 * @var    \WP_Filesystem_Direct
	 * @access private
	 * @author Grégory Viguier
	 */
	private $filesystem;

	/**
	 * Constructor.
	 *
	 * @since  3.4
	 * @access public
	 * @author Remy Perona
	 *
	 * @param Options_Data  $options       Options instance.
	 * @param CDNSubscriber $cdn_subsciber CDNSubscriber instance.
	 */
	public function __construct( Options_Data $options, CDNSubscriber $cdn_subsciber ) {
		$this->options       = $options;
		$this->cdn_subsciber = $cdn_subsciber;
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.4
	 * @access public
	 * @author Remy Perona
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_buffer'                   => [ 'convert_to_webp', 23 ],
			'rocket_webp_section_description' => 'webp_section_description',
			'rocket_disable_webp_cache'       => 'maybe_disable_webp_cache',
			'rocket_third_party_webp_change'  => 'sync_webp_cache_with_third_party_plugins',
		];
	}

	/** ----------------------------------------------------------------------------------------- */
	/** HOOKS =================================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Converts images extension to WebP if the file exists.
	 *
	 * @since  3.4
	 * @access public
	 * @author Remy Perona
	 * @author Grégory Viguier
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function convert_to_webp( $html ) {
		if ( ! $this->options->get( 'cache_webp' ) || $this->get_plugins_serving_webp() ) {
			return $html;
		}

		$extensions      = $this->get_extensions();
		$attribute_names = $this->get_attribute_names();

		if ( ! $extensions || ! $attribute_names ) {
			return $html;
		}

		$extensions      = implode( '|', $extensions );
		$attribute_names = implode( '|', $attribute_names );

		if ( ! preg_match_all( '@["\'\s](?<name>(?:data-[a-z0-9_-]*)?(?:' . $attribute_names . '))\s*=\s*["\']\s*(?<value>(?:https?:/)?/[^"\']+\.(?:' . $extensions . ')[^"\']*?)\s*["\']@is', $html, $attributes, PREG_SET_ORDER ) ) {
			return $html;
		}

		if ( ! isset( $this->filesystem ) ) {
			$this->filesystem = \rocket_direct_filesystem();
		}

		$result = [];

		foreach ( $attributes as $attribute ) {
			if ( preg_match( '@srcset$@i', strtolower( $attribute['name'] ) ) ) {
				/**
				 * This is a srcset attribute, with probably multiple URLs.
				 */
				$new_value = $this->srcset_to_webp( $new_value, $extensions );
			} else {
				/**
				 * A single URL attibute.
				 */
				$new_value = $this->url_to_webp( $attribute['value'], $extensions );
			}

			if ( ! $new_value ) {
				// No webp here.
				continue;
			}

			// Replace in content.
			$new_attr = preg_replace( '@' . $attribute['name'] . '\s*=\s*["\'][^"\']+["\']@s', $attribute['name'] . '="' . $new_value . '"', $attribute[0] );
			$html     = str_replace( $attribute[0], $new_attr, $html );
		}

		return $html;
	}

	/**
	 * Modifies the WebP section description of WP Rocket settings
	 *
	 * @since  3.4
	 * @access public
	 * @author Remy Perona
	 * @author Grégory Viguier
	 *
	 * @param string $description Section description.
	 * @return string
	 */
	public function webp_section_description( $description ) {
		$webp_plugins = $this->get_webp_plugins();
		$serving      = [];
		$creating     = [];
		$info_url     = '';

		if ( $webp_plugins ) {
			foreach ( $webp_plugins as $plugin ) {
				if ( $plugin->is_serving_webp() ) {
					$serving[ $plugin->get_id() ] = $plugin->get_name();
				} elseif ( ! $serving && $plugin->is_converting_to_webp() ) {
					$creating[ $plugin->get_id() ] = $plugin->get_name();
				}
			}
		}

		if ( $serving ) {
			return sprintf(
				// Translators: %1$s = plugin name(s), %2$s = opening link tag, %3$s = closing link tag.
				_n( 'You are using %1$s to serve images as WebP. WP Rocket will NOT create a dedicated cache for WebP support. %2$sMore info%3$s', 'You are using %1$s to serve images as WebP. WP Rocket will NOT create a dedicated cache for WebP support. %2$sMore info%3$s', count( $serving ), 'rocket' ),
				wp_sprintf_l( '%l', $serving ),
				'<a href="' . $info_url . '">',
				'</a>'
			);
		}

		/** This filter is documented in inc/classes/buffer/class-cache.php */
		if ( apply_filters( 'rocket_disable_webp_cache', false ) ) {
			return __( 'WebP cache is disabled by filter.', 'rocket' );
		}

		if ( $creating ) {
			return sprintf(
				// Translators: %1$s = plugin name(s), %2$s = opening link tag, %3$s = closing link tag.
				_n( 'You are using %1$s to convert images to WebP. WP Rocket will create a dedicated cache for WebP support. %2$sMore info%3$s', 'You are using %1$s to convert images to WebP. WP Rocket will create a dedicated cache for WebP support. %2$sMore info%3$s', count( $creating ), 'rocket' ),
				wp_sprintf_l( '%l', $creating ),
				'<a href="' . $info_url . '">',
				'</a>'
			);
		}

		return sprintf(
			// Translators: %1$s and %2$s = opening link tag, %3$s = closing link tag.
			__( 'You are not using a method to convert and serve images as WebP that WP Rocket supports. Consider using %1$sImagify%3$s or another supported plugin. %2$sMore info%3$s', 'rocket' ),
			'<a href="https://wordpress.org/plugins/imagify/">',
			'<a href="' . $info_url . '">',
			'</a>'
		);
	}

	/**
	 * Disable the WebP cache if a WebP plugin is in use.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  bool $disable_webp_cache True to allow WebP cache (default). False otherwise.
	 * @return bool
	 */
	public function maybe_disable_webp_cache( $disable_webp_cache ) {
		return ! $disable_webp_cache && $this->get_plugins_serving_webp() ? true : $disable_webp_cache;
	}

	/**
	 * When a 3rd party plugin enables or disables its webp feature, disable or enable WPR feature accordingly.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param bool $active True if the webp feature is now active in the 3rd party plugin. False otherwise.
	 */
	public function sync_webp_cache_with_third_party_plugins( $active ) {
		rocket_generate_config_file();
	}

	/** ----------------------------------------------------------------------------------------- */
	/** TOOLS =================================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Get the list of file extensions that may have a webp version.
	 *
	 * @since  3.4
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @return array
	 */
	private function get_extensions() {
		$extensions = [ 'jpg', 'jpeg', 'jpe', 'png', 'gif' ];

		/**
		 * Filter the list of file extensions that may have a webp version.
		 *
		 * @since  3.4
		 * @author Grégory Viguier
		 *
		 * @param array $extensions An array of file extensions.
		 */
		$extensions = apply_filters( 'rocket_file_extensions_for_webp', $extensions );
		$extensions = array_filter(
			(array) $extensions,
			function( $extension ) {
				return $extension && is_string( $extension );
			}
		);

		return array_unique( $extensions );
	}

	/**
	 * Get the names of the HTML attributes where WP Rocket must search for image files.
	 *
	 * @since  3.4
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @return array
	 */
	private function get_attribute_names() {
		$attributes = [ 'href', 'src', 'srcset', 'content' ];

		/**
		 * Filter the names of the HTML attributes where WP Rocket must search for image files.
		 * Don't prepend new names with `data-`, WPR will do it. For example if you want to add `data-foo-bar`, you only need to add `foo-bar` or `bar` to the list.
		 *
		 * @since  3.4
		 * @author Grégory Viguier
		 *
		 * @param array $attributes An array of HTML attribute names.
		 */
		$attributes = apply_filters( 'rocket_attributes_for_webp', $attributes );
		$attributes = array_filter(
			(array) $attributes,
			function( $attributes ) {
				return $attributes && is_string( $attributes );
			}
		);

		return array_unique( $attributes );
	}

	/**
	 * Convert a URL to an absolute path.
	 *
	 * @since  3.4
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @param  string $url URL to convert.
	 * @return string|bool
	 */
	private function url_to_path( $url ) {
		static $hosts, $subdir_levels;

		$url_host = wp_parse_url( $url, PHP_URL_HOST );

		// Relative path.
		if ( null === $url_host ) {
			if ( ! isset( $subdir_levels ) ) {
				$subdir_levels = substr_count( preg_replace( '@^https?://@', '', site_url() ), '/' );
			}

			if ( $subdir_levels ) {
				$url = ltrim( $url, '/' );
				$url = explode( '/', $url );
				array_splice( $url, 0, $subdir_levels );
				$url = implode( '/', $url );
			}

			$url = site_url( $url );
		}

		// CDN.
		if ( ! isset( $hosts ) ) {
			$hosts = $this->cdn_subscriber->get_cdn_hosts( [], [ 'all', 'images' ] );
			$hosts = array_flip( $hosts );
		}

		if ( isset( $hosts[ $url_host ] ) ) {
			$url = preg_replace( '@^https?://' . $url_host . '/@', site_url( '/' ), $url );
		}

		// URL to path.
		$url   = preg_replace( '@^https?:@', '', $url );
		$paths = $this->get_url_to_path_associations();

		if ( ! $paths ) {
			// Uh?
			return false;
		}

		foreach ( $paths as $asso_url => $asso_path ) {
			if ( 0 === strpos( $url, $asso_url ) ) {
				$file = str_replace( $asso_url, $asso_path, $url );
				break;
			}
		}

		if ( empty( $file ) ) {
			return false;
		}

		/** This filter is documented in inc/functions/formatting.php. */
		return (string) apply_filters( 'rocket_url_to_path', $file, $url );
	}

	/**
	 * Add a webp extension to a URL.
	 *
	 * @since  3.4
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @param  string $url        A URL (I see you're very surprised).
	 * @param  string $extensions Allowed image extensions.
	 * @return string|bool        The same URL with a webp extension if the file exists. False if the webp image doesn't exist.
	 */
	private function url_to_webp( $url, $extensions ) {
		if ( ! preg_match( '@^(?<src>.+\.(?<extension>' . $extensions . '))(?<query>(?:\?.*)?)$@i', $url, $src_url ) ) {
			// Probably something like "image.jpg.webp".
			return false;
		}

		$src_path = $this->url_to_path( $src_url['src'] );

		if ( ! $src_path ) {
			return false;
		}

		$src_path_webp = preg_replace( '@\.' . $src_url['extension'] . '$@', '.webp', $src_path );

		if ( $this->filesystem->exists( $src_path_webp ) ) {
			// File name: image.jpg => image.webp.
			return preg_replace( '@\.' . $src_url['extension'] . '$@', '.webp', $src_url['src'] ) . $src_url['query'];
		}

		if ( $this->filesystem->exists( $src_path . '.webp' ) ) {
			// File name: image.jpg => image.jpg.webp.
			return $src_url['src'] . '.webp' . $src_url['query'];
		}

		return false;
	}

	/**
	 * Add webp extension to URLs in a srcset attribute.
	 *
	 * @since  3.4
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @param  array|string $srcset_values Value of a srcset attribute.
	 * @param  string       $extensions    Allowed image extensions.
	 * @return string|bool                 An array similar to $srcset_values, with webp extensions when the files exist. False if no images have webp versions.
	 */
	private function srcset_to_webp( $srcset_values, $extensions ) {
		if ( ! $srcset_values ) {
			return false;
		}

		if ( ! is_array( $srcset_values ) ) {
			$srcset_values = explode( ',', $srcset_values );
		}

		$has_webp = false;

		foreach ( $srcset_values as $i => $srcset_value ) {
			$srcset_value = preg_split( '/\s+/', trim( $srcset_value ) );

			if ( count( $srcset_value ) > 2 ) {
				// Not a good idea to have space characters in file name.
				$descriptor   = array_pop( $srcset_value );
				$srcset_value = [
					'url'        => implode( ' ', $srcset_value ),
					'descriptor' => $descriptor,
				];
			} else {
				$srcset_value = [
					'url'        => $srcset_value[0],
					'descriptor' => ! empty( $srcset_value[1] ) ? $srcset_value[1] : '1x',
				];
			}

			$url_webp = $this->url_to_webp( $srcset_value['url'], $extensions );

			if ( ! $url_webp ) {
				$srcset_values[ $i ] = implode( ' ', $srcset_value );
				continue;
			}

			$srcset_values[ $i ] = $url_webp . ' ' . $srcset_value['descriptor'];
			$has_webp            = true;
		}

		if ( ! $has_webp ) {
			return false;
		}

		return implode( ',', $srcset_values );
	}

	/**
	 * Get a list of URL/path associations.
	 * URLs are schema-less, starting by a double slash.
	 *
	 * @since  3.4
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @return array A list of URLs as keys and paths as values.
	 */
	private function get_url_to_path_associations() {
		static $list;

		if ( isset( $list ) ) {
			return $list;
		}

		$content_url = preg_replace( '@^https?:@', '', content_url( '/' ) );
		$content_dir = trailingslashit( WP_CONTENT_DIR );
		$list        = [ $content_url => $content_dir ];

		/**
		 * Filter the list of URL/path associations.
		 * The URLs with the most levels must come first.
		 *
		 * @since  3.4
		 * @author Grégory Viguier
		 *
		 * @param array $list The list of URL/path associations. URLs are schema-less, starting by a double slash.
		 */
		$list = apply_filters( 'rocket_url_to_path_associations', $list );
		$list = array_filter(
			$list,
			function( $path, $url ) {
				return $path && $url && is_string( $path ) && is_string( $url );
			},
			ARRAY_FILTER_USE_BOTH
		);

		if ( $list ) {
			$list = array_unique( $list );
		}

		return $list;
	}

	/**
	 * Get a list of plugins that serve webp images on frontend.
	 *
	 * @since  3.4
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @return array The WebP plugin names.
	 */
	private function get_plugins_serving_webp() {
		$webp_plugins = $this->get_webp_plugins();

		if ( ! $webp_plugins ) {
			// Somebody probably messed up.
			return [];
		}

		$checks = [];

		foreach ( $webp_plugins as $plugin ) {
			if ( $plugin->is_serving_webp() ) {
				$checks[ $plugin->get_id() ] = $plugin->get_name();
			}
		}

		return $checks;
	}

	/**
	 * Get a list of active plugins that convert and/or serve webp images.
	 *
	 * @since  3.4
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @return array An array of Webp_Interface objects.
	 */
	private function get_webp_plugins() {
		/**
		 * Add Webp plugins.
		 *
		 * @since  3.4
		 * @author Grégory Viguier
		 *
		 * @param array $webp_plugins An array of Webp_Interface objects.
		 */
		$webp_plugins = (array) apply_filters( 'rocket_webp_plugins', [] );

		if ( ! $webp_plugins ) {
			// Somebody probably messed up.
			return [];
		}

		foreach ( $webp_plugins as $i => $plugin ) {
			if ( ! is_a( $plugin, '\WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Webp_Interface' ) ) {
				unset( $webp_plugins[ $i ] );
				continue;
			}
			if ( ! $this->is_plugin_active( $plugin->get_basename() ) ) {
				unset( $webp_plugins[ $i ] );
				continue;
			}
		}

		return $webp_plugins;
	}

	/**
	 * Tell if a plugin is active.
	 *
	 * @since  3.4
	 * @access public
	 * @see    \plugin_basename()
	 * @author Grégory Viguier
	 *
	 * @param  string $plugin_basename A plugin basename.
	 * @return bool
	 */
	private function is_plugin_active( $plugin_basename ) {
		if ( \doing_action( 'deactivate_' . $plugin_basename ) ) {
			return false;
		}

		if ( \doing_action( 'activate_' . $plugin_basename ) ) {
			return true;
		}

		return \rocket_is_plugin_active( $plugin_basename );
	}
}
