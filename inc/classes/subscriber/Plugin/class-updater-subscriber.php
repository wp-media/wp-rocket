<?php
namespace WP_Rocket\Subscriber\Plugin;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Manages the plugin updates.
 *
 * @since  3.3.6
 * @author Grégory Viguier
 */
class Updater_Subscriber implements Subscriber_Interface {
	use \WP_Rocket\Traits\Updater_Api_Tools;

	/**
	 * Full path to the plugin.
	 *
	 * @var    string
	 * @since  3.3.6
	 * @access private
	 * @author Grégory Viguier
	 */
	private $plugin_file;

	/**
	 * Current version of the plugin.
	 *
	 * @var    string
	 * @since  3.3.6
	 * @access private
	 * @author Grégory Viguier
	 */
	private $plugin_version;

	/**
	 * URL to the plugin provider.
	 *
	 * @var    string
	 * @since  3.3.6
	 * @access private
	 * @author Grégory Viguier
	 */
	private $vendor_url;

	/**
	 * URL to contact to get update info.
	 *
	 * @var    string
	 * @since  3.3.6
	 * @access private
	 * @author Grégory Viguier
	 */
	private $api_url;

	/**
	 * A list of plugin’s icon URLs.
	 *
	 * @var    array {
	 *     @type string $2x  URL to the High-DPI size (png or jpg). Optional.
	 *     @type string $1x  URL to the normal icon size (png or jpg). Mandatory.
	 *     @type string $svg URL to the svg version of the icon. Optional.
	 * }
	 * @since  3.3.6
	 * @access private
	 * @see    https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/#plugin-icons
	 * @author Grégory Viguier
	 */
	private $icons;

	/**
	 * An ID to use when a API request fails.
	 *
	 * @var    string
	 * @since  3.3.6
	 * @access protected
	 * @author Grégory Viguier
	 */
	protected $request_error_id = 'rocket_update_failed';

	/**
	 * Name of the transient that caches the update data.
	 *
	 * @var    string
	 * @since  3.3.6
	 * @access protected
	 * @author Grégory Viguier
	 */
	protected $cache_transient_name = 'wp_rocket_update_data';

	/**
	 * Constructor
	 *
	 * @since  3.3.6
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param array $args {
	 *     Required arguments to populate the class properties.
	 *
	 *     @type string $plugin_file    Full path to the plugin.
	 *     @type string $plugin_version Current version of the plugin.
	 *     @type string $vendor_url     URL to the plugin provider.
	 *     @type string $api_url        URL to contact to get update info.
	 * }
	 */
	public function __construct( $args ) {
		foreach ( [ 'plugin_file', 'plugin_version', 'vendor_url', 'api_url', 'icons' ] as $setting ) {
			if ( isset( $args[ $setting ] ) ) {
				$this->$setting = $args[ $setting ];
			}
		}
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.3.6
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'http_request_args'                     => [ 'exclude_rocket_from_wp_updates', 5, 2 ],
			'pre_set_site_transient_update_plugins' => 'maybe_add_rocket_update_data',
			'deleted_site_transient'                => 'maybe_delete_rocket_update_data_cache',
			'wp_rocket_loaded'                      => 'maybe_force_check',
		];
	}

	/** ----------------------------------------------------------------------------------------- */
	/** PLUGIN UPDATE DATA ====================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * When WP checks plugin versions against the latest versions hosted on WordPress.org, remove WPR from the list.
	 *
	 * @since  3.3.6
	 * @access public
	 * @see    wp_update_plugins()
	 * @author Grégory Viguier
	 *
	 * @param  array  $request An array of HTTP request arguments.
	 * @param  string $url     The request URL.
	 * @return array           Updated array of HTTP request arguments.
	 */
	public function exclude_rocket_from_wp_updates( $request, $url ) {
		if ( ! is_string( $url ) ) {
			return $request;
		}

		if ( ! preg_match( '@^https?://api.wordpress.org/plugins/update-check(/|\?|$)@', $url ) || empty( $request['body']['plugins'] ) ) {
			// Not a plugin update request. Stop immediately.
			return $request;
		}

		/**
		 * Depending on the API version, the data can have several forms:
		 * - Can be serialized or JSON encoded,
		 * - Can be an object of arrays or an object of objects.
		 */
		$is_serialized = is_serialized( $request['body']['plugins'] );
		$basename      = plugin_basename( $this->plugin_file );
		$edited        = false;

		if ( $is_serialized ) {
			$plugins = maybe_unserialize( $request['body']['plugins'] );
		} else {
			$plugins = json_decode( $request['body']['plugins'] );
		}

		if ( ! empty( $plugins->plugins ) ) {
			if ( is_object( $plugins->plugins ) ) {
				if ( isset( $plugins->plugins->$basename ) ) {
					unset( $plugins->plugins->$basename );
					$edited = true;
				}
			} elseif ( is_array( $plugins->plugins ) ) {
				if ( isset( $plugins->plugins[ $basename ] ) ) {
					unset( $plugins->plugins[ $basename ] );
					$edited = true;
				}
			}
		}

		if ( ! empty( $plugins->active ) ) {
			$active_is_object = is_object( $plugins->active );

			if ( $active_is_object || is_array( $plugins->active ) ) {
				foreach ( $plugins->active as $key => $plugin_basename ) {
					if ( $plugin_basename !== $basename ) {
						continue;
					}
					if ( $active_is_object ) {
						unset( $plugins->active->$key );
					} else {
						unset( $plugins->active[ $key ] );
					}
					$edited = true;
					break;
				}
			}
		}

		if ( $edited ) {
			if ( $is_serialized ) {
				$request['body']['plugins'] = maybe_serialize( $plugins );
			} else {
				$request['body']['plugins'] = wp_json_encode( $plugins );
			}
		}

		return $request;
	}

	/**
	 * Add WPR update data to the "WP update" transient.
	 *
	 * @since  3.3.6
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  \stdClass $transient_value New value of site transient.
	 * @return \stdClass
	 */
	public function maybe_add_rocket_update_data( $transient_value ) {
		if ( defined( 'WP_INSTALLING' ) ) {
			return $transient_value;
		}

		// Get the remote version data.
		$remote_data = $this->get_cached_latest_version_data();

		if ( is_wp_error( $remote_data ) ) {
			return $transient_value;
		}

		// Make sure the transient value is well formed.
		if ( ! is_object( $transient_value ) ) {
			$transient_value = new \stdClass();
		}

		if ( empty( $transient_value->response ) ) {
			$transient_value->response = [];
		}

		if ( empty( $transient_value->checked ) ) {
			$transient_value->checked = [];
		}

		// If a newer version is available, add the update.
		if ( version_compare( $this->plugin_version, $remote_data->new_version, '<' ) ) {
			$transient_value->response[ $remote_data->plugin ] = $remote_data;
		}

		$transient_value->checked[ $remote_data->plugin ] = $this->plugin_version;

		return $transient_value;
	}

	/**
	 * Delete WPR update data cache when the "WP update" transient is deleted.
	 *
	 * @since  3.3.6
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param string $transient_name Deleted transient name.
	 */
	public function maybe_delete_rocket_update_data_cache( $transient_name ) {
		if ( 'update_plugins' === $transient_name ) {
			$this->delete_rocket_update_data_cache();
		}
	}

	/**
	 * If the `rocket_force_update` query arg is set, force WP to refresh the list of plugins to update.
	 *
	 * @since  3.3.6
	 * @access public
	 * @author Grégory Viguier
	 */
	public function maybe_force_check() {
		if ( is_string( filter_input( INPUT_GET, 'rocket_force_update' ) ) ) {
			delete_site_transient( 'update_plugins' );
		}
	}

	/** ----------------------------------------------------------------------------------------- */
	/** TOOLS =================================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Get the latest WPR update data from our server.
	 *
	 * @since  3.3.6
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return \stdClass|\WP_Error {
	 *     A \WP_Error object on failure. An object on success:
	 *
	 *     @type string $slug        The plugin slug.
	 *     @type string $plugin      The plugin base name.
	 *     @type string $new_version The plugin new version.
	 *     @type string $url         URL to the plugin provider.
	 *     @type string $package     URL to the zip file of the new version.
	 *     @type array  $icons       {
	 *         A list of plugin’s icon URLs.
	 *
	 *         @type string $2x  URL to the High-DPI size (png or jpg). Optional.
	 *         @type string $1x  URL to the normal icon size (png or jpg). Mandatory.
	 *         @type string $svg URL to the svg version of the icon. Optional.
	 *     }
	 * }
	 */
	public function get_latest_version_data() {
		$request = wp_remote_get(
			$this->api_url,
			[
				'timeout' => 30,
			]
		);

		if ( is_wp_error( $request ) ) {
			return $this->get_request_error(
				[
					'error_code' => $request->get_error_code(),
					'response'   => $request->get_error_message(),
				]
			);
		}

		$res  = trim( wp_remote_retrieve_body( $request ) );
		$code = wp_remote_retrieve_response_code( $request );

		if ( 200 !== $code ) {
			/**
			 * If the response doesn’t have a status 200: it is an error, or there is no new update.
			 */
			return $this->get_request_error(
				[
					'http_code' => $code,
					'response'  => $res,
				]
			);
		}

		/**
		 * This will match:
		 * - `2.3.4.5-beta1||1.2.3.4-beta2||||||||||||||||||||||||||||||||`: expired license.
		 * - `2.3.4.5-beta1|https://wp-rocket.me/i-should-write-a-funny-thing-here/wp-rocket_1.2.3.4-beta2.zip|1.2.3.4-beta2`: valid license.
		 */
		if ( ! preg_match( '@^(?<stable_version>\d+(?:\.\d+){1,3}[^|]*)\|(?<package>(?:http.+\.zip)?)\|(?<user_version>\d+(?:\.\d+){1,3}[^|]*)(?:\|+)?$@', $res, $match ) ) {
			/**
			 * If the response doesn’t have the right format, it is an error.
			 */
			return $this->get_request_error( $res );
		}

		$obj = new \stdClass();

		$obj->slug        = $this->get_plugin_slug( $this->plugin_file );
		$obj->plugin      = plugin_basename( $this->plugin_file );
		$obj->new_version = $match['user_version'];
		$obj->url         = $this->vendor_url;
		$obj->package     = $match['package'];

		if ( $this->icons && ! empty( $this->icons['1x'] ) ) {
			$obj->icons = $this->icons;
		}

		return $obj;
	}

	/**
	 * Get the cached version of the latest WPR update data.
	 *
	 * @since  3.3.6
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return \stdClass|\WP_Error {
	 *     A \WP_Error object on failure. An object on success:
	 *
	 *     @type string $slug        The plugin slug.
	 *     @type string $plugin      The plugin base name.
	 *     @type string $new_version The plugin new version.
	 *     @type string $url         URL to the plugin provider.
	 *     @type string $package     URL to the zip file of the new version.
	 *     @type array  $icons       {
	 *         A list of plugin’s icon URLs.
	 *
	 *         @type string $2x  URL to the High-DPI size (png or jpg). Optional.
	 *         @type string $1x  URL to the normal icon size (png or jpg). Mandatory.
	 *         @type string $svg URL to the svg version of the icon. Optional.
	 *     }
	 * }
	 */
	public function get_cached_latest_version_data() {
		static $response;

		if ( isset( $response ) ) {
			// "force update" won’t bypass the static cache: only one http request by page load.
			return $response;
		}

		$force_update = is_string( filter_input( INPUT_GET, 'rocket_force_update' ) );

		if ( ! $force_update ) {
			// No "force update": try to get the result from a transient.
			$response = get_site_transient( $this->cache_transient_name );

			if ( $response && is_object( $response ) ) {
				// Got something in cache.
				return $response;
			}
		}

		// Get fresh data.
		$response       = $this->get_latest_version_data();
		$cache_duration = 12 * HOUR_IN_SECONDS;

		if ( is_wp_error( $response ) ) {
			$error_data = $response->get_error_data();

			if ( ! empty( $error_data['error_code'] ) ) {
				// `wp_remote_get()` returned an internal error ('error_code' contains a WP_Error code ).
				$cache_duration = HOUR_IN_SECONDS;
			} elseif ( ! empty( $error_data['http_code'] ) && $error_data['http_code'] >= 400 ) {
				// We got a 4xx or 5xx HTTP error.
				$cache_duration = 2 * HOUR_IN_SECONDS;
			}
		}

		set_site_transient( $this->cache_transient_name, $response, $cache_duration );

		return $response;
	}

	/**
	 * Delete WP Rocket update data cache.
	 *
	 * @since  3.3.6
	 * @access public
	 * @author Grégory Viguier
	 */
	public function delete_rocket_update_data_cache() {
		delete_site_transient( $this->cache_transient_name );
	}
}
