<?php
namespace WP_Rocket\Subscriber\Media;

use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Engine\CDN\Subscriber as CDNSubscriber;

/**
 * Subscriber for the WebP support.
 *
 * @since  3.4
 * @author Remy Perona
 * @author Grégory Viguier
 */
class Webp_Subscriber implements Subscriber_Interface {
	/**
	 * Options_Data instance.
	 *
	 * @var    Options_Data
	 * @access private
	 * @author Remy Perona
	 */
	private $options_data;

	/**
	 * Options instance.
	 *
	 * @var    Options
	 * @access private
	 * @author Grégory Viguier
	 */
	private $options_api;

	/**
	 * CDNSubscriber instance.
	 *
	 * @var    CDNSubscriber
	 * @access private
	 * @author Grégory Viguier
	 */
	private $cdn_subscriber;

	/**
	 * Beacon instance
	 *
	 * @var    Beacon
	 * @access private
	 * @author Grégory Viguier
	 */
	private $beacon;

	/**
	 * Values of $_SERVER to use for some tests.
	 *
	 * @var    array
	 * @access private
	 * @author Grégory Viguier
	 */
	private $server;

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
	 * @param Options_Data  $options_data   Options_Data instance.
	 * @param Options       $options_api    Options instance.
	 * @param CDNSubscriber $cdn_subscriber CDNSubscriber instance.
	 * @param Beacon        $beacon         Beacon instance.
	 * @param array         $server         Values of $_SERVER to use for the tests. Default is $_SERVER.
	 */
	public function __construct( Options_Data $options_data, Options $options_api, CDNSubscriber $cdn_subscriber, Beacon $beacon, $server = null ) {
		$this->options_data   = $options_data;
		$this->options_api    = $options_api;
		$this->cdn_subscriber = $cdn_subscriber;
		$this->beacon         = $beacon;

		if ( ! isset( $server ) && ! empty( $_SERVER ) && is_array( $_SERVER ) ) {
			$server = $_SERVER;
		}

		$this->server = $server && is_array( $server ) ? $server : [];
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_buffer'                     => [ 'convert_to_webp', 16 ],
			'rocket_cache_webp_setting_field'   => [
				[ 'maybe_disable_setting_field' ],
				[ 'webp_section_description' ],
			],
			'rocket_disable_webp_cache'         => 'maybe_disable_webp_cache',
			'rocket_third_party_webp_change'    => 'sync_webp_cache_with_third_party_plugins',
			'rocket_preload_before_preload_url' => 'add_accept_header',
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
		if ( ! $this->options_data->get( 'cache_webp' ) ) {
			return $html;
		}

		/** This filter is documented in inc/classes/buffer/class-cache.php */
		if ( apply_filters( 'rocket_disable_webp_cache', false ) ) {
			return $html;
		}

		// Only to supporting browsers.
		$http_accept = isset( $this->server['HTTP_ACCEPT'] ) ? $this->server['HTTP_ACCEPT'] : '';

		if ( ! $http_accept && function_exists( 'apache_request_headers' ) ) {
			$headers     = apache_request_headers();
			$http_accept = isset( $headers['Accept'] ) ? $headers['Accept'] : '';
		}

		if ( ! $http_accept || false === strpos( $http_accept, 'webp' ) ) {
			$user_agent = isset( $this->server['HTTP_USER_AGENT'] ) ? $this->server['HTTP_USER_AGENT'] : '';

			if ( $user_agent && preg_match( '#Firefox/(?<version>[0-9]{2,})#i', $this->server['HTTP_USER_AGENT'], $matches ) ) {
				if ( 66 >= (int) $matches['version'] ) {
					return $html;
				}
			} else {
				return $html;
			}
		}

		$extensions      = $this->get_extensions();
		$attribute_names = $this->get_attribute_names();

		if ( ! $extensions || ! $attribute_names ) {
			return $html . '<!-- Rocket no webp -->';
		}

		$extensions      = implode( '|', $extensions );
		$attribute_names = implode( '|', $attribute_names );

		if ( ! preg_match_all( '@["\'\s](?<name>(?:data-(?:[a-z0-9_-]+-)?)?(?:' . $attribute_names . '))\s*=\s*["\']\s*(?<value>(?:https?:/)?/[^"\']+\.(?:' . $extensions . ')[^"\']*?)\s*["\']@is', $html, $attributes, PREG_SET_ORDER ) ) {
			return $html . '<!-- Rocket no webp -->';
		}

		if ( ! isset( $this->filesystem ) ) {
			$this->filesystem = \rocket_direct_filesystem();
		}

		$has_hebp = false;

		foreach ( $attributes as $attribute ) {
			if ( preg_match( '@srcset$@i', strtolower( $attribute['name'] ) ) ) {
				/**
				 * This is a srcset attribute, with probably multiple URLs.
				 */
				$new_value = $this->srcset_to_webp( $attribute['value'], $extensions );
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
			$has_hebp = true;
			$new_attr = preg_replace( '@' . $attribute['name'] . '\s*=\s*["\'][^"\']+["\']@s', $attribute['name'] . '="' . $new_value . '"', $attribute[0] );
			$html     = str_replace( $attribute[0], $new_attr, $html );
		}

		/**
		 * Tell if the page contains webp files.
		 *
		 * @since  3.4
		 * @author Grégory Viguier
		 *
		 * @param bool   $has_hebp True if the page contains webp files. False otherwise.
		 * @param string $html     The page’s html contents.
		 */
		$has_hebp = apply_filters( 'rocket_page_has_hebp_files', $has_hebp, $html );

		// Tell the cache process if some URLs have been replaced.
		if ( $has_hebp ) {
			$html .= '<!-- Rocket has webp -->';
		} else {
			$html .= '<!-- Rocket no webp -->';
		}

		return $html;
	}

	/**
	 * Modifies the WebP section description of WP Rocket settings.
	 *
	 * @since  3.4
	 * @access public
	 * @author Remy Perona
	 * @author Grégory Viguier
	 *
	 * @param  array $cache_webp_field Section description.
	 * @return array
	 */
	public function webp_section_description( $cache_webp_field ) {
		$webp_beacon            = $this->beacon->get_suggest( 'webp' );
		$webp_plugins           = $this->get_webp_plugins();
		$serving                = [];
		$serving_not_compatible = [];
		$creating               = [];

		if ( $webp_plugins ) {
			$is_using_cdn = $this->is_using_cdn();

			foreach ( $webp_plugins as $plugin ) {
				if ( $plugin->is_serving_webp() ) {
					if ( $is_using_cdn && ! $plugin->is_serving_webp_compatible_with_cdn() ) {
						// Serving WebP using a method not compatible with CDN.
						$serving_not_compatible[ $plugin->get_id() ] = $plugin->get_name();
					} else {
						// Serving WebP when no CDN or with a method compatible with CDN.
						$serving[ $plugin->get_id() ] = $plugin->get_name();
					}
				}
				if ( $plugin->is_converting_to_webp() ) {
					// Generating WebP.
					$creating[ $plugin->get_id() ] = $plugin->get_name();
				}
			}
		}

		if ( $serving ) {
			// 5, 8.
			$cache_webp_field['description'] = sprintf(
			// Translators: %1$s = plugin name(s), %2$s = opening <a> tag, %3$s = closing </a> tag.
				esc_html( _n( 'You are using %1$s to serve WebP images so you do not need to enable this option. %2$sMore info%3$s %4$s If you prefer to have WP Rocket serve WebP for you instead, please disable WebP display in %1$s.', 'You are using %1$s to serve WebP images so you do not need to enable this option. %2$sMore info%3$s %4$s If you prefer to have WP Rocket serve WebP for you instead, please disable WebP display in %1$s.', count( $serving ), 'rocket' ) ),
				esc_html( wp_sprintf_l( '%l', $serving ) ),
				'<a href="' . esc_url( $webp_beacon['url'] ) . '" data-beacon-article="' . esc_attr( $webp_beacon['id'] ) . '" target="_blank" rel="noopener noreferrer">',
				'</a>',
				'<br>'
			);

			return $cache_webp_field;
		}

		/** This filter is documented in inc/classes/buffer/class-cache.php */
		if ( apply_filters( 'rocket_disable_webp_cache', false ) ) {
			$cache_webp_field['description'] = esc_html__( 'WebP cache is disabled by filter.', 'rocket' );

			return $cache_webp_field;
		}

		if ( $serving_not_compatible ) {
			if ( ! $this->options_data->get( 'cache_webp' ) ) {
				// 6.
				$cache_webp_field['description'] = sprintf(
				// Translators: %1$s = plugin name(s), %2$s = opening <a> tag, %3$s = closing </a> tag.
					esc_html( _n( 'You are using %1$s to convert images to WebP. If you want WP Rocket to serve them for you, activate this option. %2$sMore info%3$s', 'You are using %1$s to convert images to WebP. If you want WP Rocket to serve them for you, activate this option. %2$sMore info%3$s', count( $serving_not_compatible ), 'rocket' ) ),
					esc_html( wp_sprintf_l( '%l', $serving_not_compatible ) ),
					'<a href="' . esc_url( $webp_beacon['url'] ) . '" data-beacon-article="' . esc_attr( $webp_beacon['id'] ) . '" target="_blank" rel="noopener noreferrer">',
					'</a>'
				);

				return $cache_webp_field;
			}

			// 7.
			$cache_webp_field['description'] = sprintf(
			// Translators: %1$s = plugin name(s), %2$s = opening <a> tag, %3$s = closing </a> tag.
				esc_html( _n( 'You are using %1$s to convert images to WebP. WP Rocket will create separate cache files to serve your WebP images. %2$sMore info%3$s', 'You are using %1$s to convert images to WebP. WP Rocket will create separate cache files to serve your WebP images. %2$sMore info%3$s', count( $serving_not_compatible ), 'rocket' ) ),
				esc_html( wp_sprintf_l( '%l', $serving_not_compatible ) ),
				'<a href="' . esc_url( $webp_beacon['url'] ) . '" data-beacon-article="' . esc_attr( $webp_beacon['id'] ) . '" target="_blank" rel="noopener noreferrer">',
				'</a>'
			);

			return $cache_webp_field;
		}

		if ( $creating ) {
			if ( ! $this->options_data->get( 'cache_webp' ) ) {
				// 3.
				$cache_webp_field['description'] = sprintf(
				// Translators: %1$s = plugin name(s), %2$s = opening <a> tag, %3$s = closing </a> tag.
					esc_html( _n( 'You are using %1$s to convert images to WebP. If you want WP Rocket to serve them for you, activate this option. %2$sMore info%3$s', 'You are using %1$s to convert images to WebP. If you want WP Rocket to serve them for you, activate this option. %2$sMore info%3$s', count( $creating ), 'rocket' ) ),
					esc_html( wp_sprintf_l( '%l', $creating ) ),
					'<a href="' . esc_url( $webp_beacon['url'] ) . '" data-beacon-article="' . esc_attr( $webp_beacon['id'] ) . '" target="_blank" rel="noopener noreferrer">',
					'</a>'
				);

				return $cache_webp_field;
			}

			// 4.
			$cache_webp_field['description'] = sprintf(
			// Translators: %1$s = plugin name(s), %2$s = opening <a> tag, %3$s = closing </a> tag.
				esc_html( _n( 'You are using %1$s to convert images to WebP. WP Rocket will create separate cache files to serve your WebP images. %2$sMore info%3$s', 'You are using %1$s to convert images to WebP. WP Rocket will create separate cache files to serve your WebP images. %2$sMore info%3$s', count( $creating ), 'rocket' ) ),
				esc_html( wp_sprintf_l( '%l', $creating ) ),
				'<a href="' . esc_url( $webp_beacon['url'] ) . '" data-beacon-article="' . esc_attr( $webp_beacon['id'] ) . '" target="_blank" rel="noopener noreferrer">',
				'</a>'
			);

			return $cache_webp_field;
		}

		if ( ! $this->options_data->get( 'cache_webp' ) ) {
			// 1.
			if ( rocket_valid_key() && ! \Imagify_Partner::has_imagify_api_key() ) {
				$imagify_link = '<a href="#imagify">';
			} else {
				// The Imagify page is not displayed.
				$imagify_link = '<a href="https://wordpress.org/plugins/imagify/" target="_blank" rel="noopener noreferrer">';
			}

			$cache_webp_field['description'] = sprintf(
			// Translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
				esc_html__( '%5$sWe have not detected any compatible WebP plugin!%6$s%4$s If you don’t already have WebP images on your site consider using %3$sImagify%2$s or another supported plugin. %1$sMore info%2$s %4$s If you are not using WebP do not enable this option.', 'rocket' ),
				'<a href="' . esc_url( $webp_beacon['url'] ) . '" data-beacon-article="' . esc_attr( $webp_beacon['id'] ) . '" target="_blank" rel="noopener noreferrer">',
				'</a>',
				$imagify_link,
				'<br>',
				'<strong>',
				'</strong>'
			);
			return $cache_webp_field;
		}

		// 2.
		$cache_webp_field['description'] = esc_html__( 'WP Rocket will create separate cache files to serve your WebP images.', 'rocket' );

		return $cache_webp_field;
	}

	/**
	 * Disable 'cache_webp' setting field if another plugin serves WebP.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  array $cache_webp_field Data to be added to the setting field.
	 * @return array
	 */
	public function maybe_disable_setting_field( $cache_webp_field ) {
		/** This filter is documented in inc/classes/buffer/class-cache.php */
		if ( ! apply_filters( 'rocket_disable_webp_cache', false ) ) {
			return $cache_webp_field;
		}

		foreach ( [ 'input_attr', 'container_class' ] as $attr ) {
			if ( ! isset( $cache_webp_field[ $attr ] ) || ! is_array( $cache_webp_field[ $attr ] ) ) {
				$cache_webp_field[ $attr ] = [];
			}
		}

		$cache_webp_field['input_attr']['disabled'] = 1;

		return $cache_webp_field;
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
		return ! $disable_webp_cache && $this->get_plugins_serving_webp() ? true : (bool) $disable_webp_cache;
	}

	/**
	 * When a 3rd party plugin enables or disables its webp feature, disable or enable WPR feature accordingly.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 */
	public function sync_webp_cache_with_third_party_plugins() {
		if ( $this->options_data->get( 'cache_webp' ) && $this->get_plugins_serving_webp() ) {
			// Disable the cache webp option.
			$this->options_data->set( 'cache_webp', 0 );
			$this->options_api->set( 'settings', $this->options_data->get_options() );
		}
		rocket_generate_config_file();
	}

	/**
	 * Add WebP to the HTTP_ACCEPT headers on preload request when the WebP option is active
	 *
	 * @param array $requests Requests to make.
	 * @return array
	 */
	public function add_accept_header( $requests ) {

		if ( ! is_array( $requests ) || ! $this->options_data->get( 'cache_webp' ) ) {
			return $requests;
		}

		return array_map(
			function ( $request ) {
				$request['headers']['headers']['Accept']      = 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8';
				$request['headers']['headers']['HTTP_ACCEPT'] = 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8';
				return $request;
			},
			$requests
			);
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
	 * Get the names of the HTML attributes where WP Rocket must search for image files.
	 *
	 * @since  3.4
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @return array
	 */
	private function get_attribute_names() {
		$attributes = [ 'href', 'src', 'srcset', 'data-large_image', 'data-thumb' ];

		/**
		 * Filter the names of the HTML attributes where WP Rocket must search for image files.
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
		static $hosts, $site_host, $subdir_levels;

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
			if ( ! isset( $site_host ) ) {
				$site_host = wp_parse_url( site_url( '/' ), PHP_URL_HOST );
			}
			if ( $site_host ) {
				$url = preg_replace( '@^(https?://)' . $url_host . '/@', '$1' . $site_host . '/', $url );
			}
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
		$content_dir = trailingslashit( rocket_get_constant( 'WP_CONTENT_DIR' ) );
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
	 * If the CDN is used, this won't list plugins that use a technique not compatible with CDN.
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

		$checks       = [];
		$is_using_cdn = $this->is_using_cdn();

		foreach ( $webp_plugins as $plugin ) {
			if ( $is_using_cdn && $plugin->is_serving_webp_compatible_with_cdn() ) {
				$checks[ $plugin->get_id() ] = $plugin->get_name();
			} elseif ( ! $is_using_cdn && $plugin->is_serving_webp() ) {
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

	/**
	 * Tell if WP Rocket uses a CDN for images.
	 *
	 * @since  3.4
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	private function is_using_cdn() {
		// Don't use `$this->options_data->get( 'cdn' )` here, we need an up-to-date value when the CDN option changes.
		$use = get_rocket_option( 'cdn' ) && $this->cdn_subscriber->get_cdn_hosts( [], [ 'all', 'images' ] );
		/**
		 * Filter whether WP Rocket is using a CDN for webp images.
		 *
		 * @since  3.4
		 * @author Grégory Viguier
		 *
		 * @param bool $use True if WP Rocket is using a CDN for webp images. False otherwise.
		 */
		return (bool) apply_filters( 'rocket_webp_is_using_cdn', $use );
	}
}
