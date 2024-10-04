<?php
namespace WP_Rocket\Engine\Plugin;

use Plugin_Upgrader;
use Plugin_Upgrader_Skin;
use WP_Error;
use WP_Rocket\Event_Management\{Event_Manager, Event_Manager_Aware_Subscriber_Interface};

/**
 * Manages the plugin updates.
 */
class UpdaterSubscriber implements Event_Manager_Aware_Subscriber_Interface {
	use UpdaterApiTools;

	const UPDATE_ENDPOINT = 'https://api.wp-rocket.me/check_update.php';

	/**
	 * Full path to the plugin.
	 *
	 * @var string
	 */
	private $plugin_file;

	/**
	 * Current version of the plugin.
	 *
	 * @var string
	 */
	private $plugin_version;

	/**
	 * URL to the plugin provider.
	 *
	 * @var string
	 */
	private $vendor_url;

	/**
	 * A list of plugin’s icon URLs.
	 *
	 * @var array {
	 *     @type string $2x  URL to the High-DPI size (png or jpg). Optional.
	 *     @type string $1x  URL to the normal icon size (png or jpg). Mandatory.
	 *     @type string $svg URL to the svg version of the icon. Optional.
	 * }
	 * @see https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/#plugin-icons
	 */
	private $icons;

	/**
	 * An ID to use when a API request fails.
	 *
	 * @var string
	 */
	protected $request_error_id = 'rocket_update_failed';

	/**
	 * Name of the transient that caches the update data.
	 *
	 * @var string
	 */
	protected $cache_transient_name = 'wp_rocket_update_data';

	/**
	 * The WordPress Event Manager
	 *
	 * @var Event_Manager
	 */
	protected $event_manager;

	/**
	 * RenewalNotice instance
	 *
	 * @var RenewalNotice
	 */
	private $renewal_notice;

	/**
	 * Constructor
	 *
	 * @param RenewalNotice $renewal_notice RenewalNotice instance.
	 *
	 * @param array         $args { Required arguments to populate the class properties.
	 *     @type string $plugin_file    Full path to the plugin.
	 *     @type string $plugin_version Current version of the plugin.
	 *     @type string $vendor_url     URL to the plugin provider.
	 * }
	 */
	public function __construct( RenewalNotice $renewal_notice, $args ) {
		foreach ( [ 'plugin_file', 'plugin_version', 'vendor_url', 'icons' ] as $setting ) {
			if ( isset( $args[ $setting ] ) ) {
				$this->$setting = $args[ $setting ];
			}
		}

		$this->renewal_notice = $renewal_notice;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param Event_Manager $event_manager The WordPress Event Manager.
	 */
	public function set_event_manager( Event_Manager $event_manager ) {
		$this->event_manager = $event_manager;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_subscribed_events() {
		return [
			'http_request_args'                        => [ 'exclude_rocket_from_wp_updates', 5, 2 ],
			'pre_set_site_transient_update_plugins'    => 'maybe_add_rocket_update_data',
			'deleted_site_transient'                   => 'maybe_delete_rocket_update_data_cache',
			'wp_rocket_loaded'                         => 'maybe_force_check',
			'auto_update_plugin'                       => [ 'disable_auto_updates', 10, 2 ],
			'admin_post_rocket_rollback'               => 'rollback',
			'upgrader_pre_install'                     => [ 'upgrade_pre_install_option', 10, 2 ],
			'upgrader_post_install'                    => [ 'upgrade_post_install_option', 10, 2 ],
			'after_plugin_row_wp-rocket/wp-rocket.php' => 'display_renewal_notice',
			'admin_print_styles-plugins.php'           => 'add_expired_styles',
		];
	}

	/**
	 * When WP checks plugin versions against the latest versions hosted on WordPress.org, remove WPR from the list.
	 *
	 * @see    wp_update_plugins()
	 *
	 * @param  array  $request An array of HTTP request arguments.
	 * @param  string $url     The request URL.
	 * @return array           Updated array of HTTP request arguments.
	 */
	public function exclude_rocket_from_wp_updates( $request, string $url ) {
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
	 * @param  \stdClass|array $transient_value New value of site transient.
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
	 * @param string $transient_name Deleted transient name.
	 */
	public function maybe_delete_rocket_update_data_cache( $transient_name ) {
		if ( 'update_plugins' === $transient_name ) {
			$this->delete_rocket_update_data_cache();
		}
	}

	/**
	 * If the `rocket_force_update` query arg is set, force WP to refresh the list of plugins to update.
	 */
	public function maybe_force_check() {
		if ( is_string( filter_input( INPUT_GET, 'rocket_force_update' ) ) ) {
			delete_site_transient( 'update_plugins' );
		}
	}

	/**
	 * Disable auto-updates for WP Rocket
	 *
	 * @param bool|null $update Whether to update. The value of null is internally used to detect whether nothing has hooked into this filter.
	 * @param object    $item The update offer.
	 * @return bool|null
	 */
	public function disable_auto_updates( $update, $item ) {
		if ( isset( $item->plugin ) && ( 'wp-rocket/wp-rocket.php' === $item->plugin ) ) {
			return false;
		}

		return $update;
	}

	/**
	 * Get the latest WPR update data from our server.
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
			self::UPDATE_ENDPOINT,
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

		$obj->slug           = $this->get_plugin_slug( $this->plugin_file );
		$obj->plugin         = plugin_basename( $this->plugin_file );
		$obj->new_version    = $match['user_version'];
		$obj->url            = $this->vendor_url;
		$obj->package        = $match['package'];
		$obj->stable_version = $match['stable_version'];

		/**
		 * Filters the WP tested version value
		 *
		 * @since 3.10.7
		 *
		 * @param string $wp_tested_version WP tested version value.
		 */
		$obj->tested = apply_filters( 'rocket_wp_tested_version', WP_ROCKET_WP_VERSION_TESTED );

		if ( $this->icons && ! empty( $this->icons['1x'] ) ) {
			$obj->icons = $this->icons;
		}

		return $obj;
	}

	/**
	 * Get the cached version of the latest WPR update data.
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
	 */
	public function delete_rocket_update_data_cache() {
		delete_site_transient( $this->cache_transient_name );
	}



	/**
	 * Do the rollback
	 *
	 * @since 2.4
	 */
	public function rollback() {
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'rocket_rollback' ) ) {
			wp_nonce_ays( '' );
		}

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			wp_nonce_ays( '' );
		}

		/**
		 * Fires before doing the rollback
		 */
		do_action( 'rocket_before_rollback' );

		$plugin_transient = get_site_transient( 'update_plugins' );
		$plugin_folder    = plugin_basename( dirname( WP_ROCKET_FILE ) );
		$plugin           = $plugin_folder . '/' . basename( WP_ROCKET_FILE );

		$plugin_transient->response[ $plugin ] = (object) [
			'slug'        => $plugin_folder,
			'new_version' => WP_ROCKET_LASTVERSION,
			'url'         => 'https://wp-rocket.me',
			'package'     => sprintf( 'https://api.wp-rocket.me/%s/wp-rocket_%s.zip', get_rocket_option( 'consumer_key' ), WP_ROCKET_LASTVERSION ),
		];

		$this->event_manager->remove_callback( 'pre_set_site_transient_update_plugins', [ $this, 'maybe_add_rocket_update_data' ] );

		set_site_transient( 'update_plugins', $plugin_transient );

		// @phpstan-ignore-next-line
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		// translators: %s is the plugin name.
		$title         = sprintf( __( '%s Update Rollback', 'rocket' ), WP_ROCKET_PLUGIN_NAME );
		$nonce         = 'upgrade-plugin_' . $plugin;
		$url           = 'update.php?action=upgrade-plugin&plugin=' . rawurlencode( $plugin );
		$upgrader_skin = new Plugin_Upgrader_Skin( compact( 'title', 'nonce', 'url', 'plugin' ) );
		$upgrader      = new Plugin_Upgrader( $upgrader_skin );

		add_filter( 'update_plugin_complete_actions', [ $this, 'rollback_add_return_link' ] );
		rocket_put_content( WP_CONTENT_DIR . '/advanced-cache.php', '' );

		$upgrader->upgrade( $plugin );

		wp_die(
			'',
			// translators: %s is the plugin name.
			esc_html( sprintf( __( '%s Update Rollback', 'rocket' ), WP_ROCKET_PLUGIN_NAME ) ),
			[
				'response' => 200,
			]
		);
	}

	/**
	 * After a rollback has been done, replace the "return to" link by a link pointing to WP Rocket's tools page.
	 * A link to the plugins page is kept in case the plugin is not reactivated correctly.
	 *
	 * @since  3.2.4
	 *
	 * @param  array $update_actions Array of plugin action links.
	 * @return array                 The array of links where the "return to" link has been replaced.
	 */
	public function rollback_add_return_link( $update_actions ) {
		if ( ! isset( $update_actions['plugins_page'] ) ) {
			return $update_actions;
		}

		$update_actions['plugins_page'] = sprintf(
			/* translators: 1 and 3 are link openings, 2 is a link closing. */
			__( '%1$sReturn to WP Rocket%2$s or %3$sgo to Plugins page%2$s', 'rocket' ),
			'<a href="' . esc_url( admin_url( 'options-general.php?page=' . WP_ROCKET_PLUGIN_SLUG ) . '#tools' ) . '" target="_parent">',
			'</a>',
			'<a href="' . esc_url( admin_url( 'plugins.php' ) ) . '" target="_parent">'
		);

		return $update_actions;
	}

	/**
	 * Set plugin option before upgrade.
	 *
	 * @param mixed $return    The result of the upgrade process.
	 * @param array $plugin    The plugin data.
	 *
	 * @return mixed|WP_Error
	 */
	public function upgrade_pre_install_option( $return, $plugin = [] ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.returnFound

		if ( is_wp_error( $return ) || ! $plugin ) {
			return $return;
		}

		$plugin = isset( $plugin['plugin'] ) ? $plugin['plugin'] : '';

		if ( empty( $plugin ) || 'wp-rocket/wp-rocket.php' !== $plugin ) {
			return $return;
		}

		set_transient( 'wp_rocket_updating', true, MINUTE_IN_SECONDS );

		return $return;
	}

	/**
	 * Update plugin option after upgrade.
	 *
	 * @param mixed $return    The result of the upgrade process.
	 * @param array $plugin    The plugin data.
	 *
	 * @return mixed|string|WP_Error
	 */
	public function upgrade_post_install_option( $return, $plugin = [] ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.returnFound
		if ( is_wp_error( $return ) || ! $plugin ) {
			return $return;
		}

		$plugin = isset( $plugin['plugin'] ) ? $plugin['plugin'] : '';
		if ( empty( $plugin ) || 'wp-rocket/wp-rocket.php' !== $plugin ) {
			return $return;
		}

		delete_transient( 'wp_rocket_updating' );

		return $return;
	}

	/**
	 * Displays Renewal notice on the plugins page
	 *
	 * @return void
	 */
	public function display_renewal_notice() {
		$latest_version_data = $this->get_cached_latest_version_data();

		if ( is_wp_error( $latest_version_data ) ) {
			return;
		}

		if ( ! isset( $latest_version_data->stable_version ) ) {
			return;
		}

		$this->renewal_notice->renewal_notice( $latest_version_data->stable_version );
	}

	/**
	 * Adds styles for expired banner
	 *
	 * @return void
	 */
	public function add_expired_styles() {
		$latest_version_data = $this->get_cached_latest_version_data();

		if ( is_wp_error( $latest_version_data ) ) {
			return;
		}

		if ( ! isset( $latest_version_data->stable_version ) ) {
			return;
		}

		$this->renewal_notice->add_expired_styles( $latest_version_data->stable_version );
	}
}
