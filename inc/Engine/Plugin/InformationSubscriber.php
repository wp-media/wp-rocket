<?php
namespace WP_Rocket\Engine\Plugin;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Manages the plugin information.
 */
class InformationSubscriber implements Subscriber_Interface {
	use UpdaterApiTools;

	/**
	 * Plugin slug.
	 *
	 * @var string
	 */
	private $plugin_slug;

	/**
	 * URL to contact to get plugin info.
	 *
	 * @var string
	 */
	private $api_url;

	/**
	 * An ID to use when a API request fails.
	 *
	 * @var string
	 */
	protected $request_error_id = 'plugins_api_failed';

	/**
	 * Constructor
	 *
	 * @param array $args {
	 *     Required arguments to populate the class properties.
	 *
	 *     @type string $plugin_file Full path to the plugin.
	 *     @type string $api_url     URL to contact to get update info.
	 * }
	 */
	public function __construct( $args ) {
		if ( isset( $args['plugin_file'] ) ) {
			$this->plugin_slug = $this->get_plugin_slug( $args['plugin_file'] );
		}
		if ( isset( $args['api_url'] ) ) {
			$this->api_url = $args['api_url'];
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_subscribed_events() {
		return [
			'plugins_api'              => [ 'exclude_rocket_from_wp_info', 10, 3 ],
			'plugins_api_result'       => [
				[ 'add_rocket_info', 10, 3 ],
				[ 'add_plugins_to_result', 11, 3 ],
			],
			'rocket_wp_tested_version' => 'add_wp_tested_version',
		];
	}

	/**
	 * Don’t ask for plugin info to the repository.
	 *
	 * @param  false|object|array $bool   The result object or array. Default false.
	 * @param  string             $action The type of information being requested from the Plugin Install API.
	 * @param  object             $args   Plugin API arguments.
	 * @return false|object|array         Empty object if slug is WP Rocket, default value otherwise.
	 */
	public function exclude_rocket_from_wp_info( $bool, $action, $args ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.boolFound
		if ( ! $this->is_requesting_rocket_info( $action, $args ) ) {
			return $bool;
		}
		return new \stdClass();
	}

	/**
	 * Insert WP Rocket plugin info.
	 *
	 * @param  object|\WP_Error $res    Response object or WP_Error.
	 * @param  string           $action The type of information being requested from the Plugin Install API.
	 * @param  object           $args   Plugin API arguments.
	 * @return object|\WP_Error         Updated response object or WP_Error.
	 */
	public function add_rocket_info( $res, $action, $args ) {
		if ( ! $this->is_requesting_rocket_info( $action, $args ) || empty( $res->external ) ) {
			return $res;
		}

		return $this->get_plugin_information();
	}

	/**
	 * Adds the WP tested version value from our API
	 *
	 * @param string $wp_tested_version WP tested version.
	 *
	 * @return string
	 */
	public function add_wp_tested_version( $wp_tested_version ): string {
		$info = $this->get_plugin_information();

		if ( empty( $info->tested ) ) {
			return $wp_tested_version;
		}

		return $info->tested;
	}

	/**
	 * Tell if requesting WP Rocket plugin info.
	 *
	 * @param  string $action The type of information being requested from the Plugin Install API.
	 * @param  object $args   Plugin API arguments.
	 * @return bool
	 */
	private function is_requesting_rocket_info( $action, $args ) {
		return ( 'query_plugins' === $action || 'plugin_information' === $action ) && isset( $args->slug ) && $args->slug === $this->plugin_slug;
	}

	/**
	 * Gets the plugin information data
	 *
	 * @return object|\WP_Error
	 */
	private function get_plugin_information() {
		$response = wp_remote_get( $this->api_url );

		if ( is_wp_error( $response ) ) {
			return $this->get_request_error( $response->get_error_message() );
		}

		$res  = maybe_unserialize( wp_remote_retrieve_body( $response ) );
		$code = wp_remote_retrieve_response_code( $response );

		if (
			200 !== $code
			||
			! ( is_object( $res ) || is_array( $res ) )
		) {
			return $this->get_request_error( wp_remote_retrieve_body( $response ) );
		}

		return $res;
	}

	/**
	 * Filter plugin fetching API results to inject Imagify
	 *
	 * @param object|WP_Error $result Response object or WP_Error.
	 * @param string          $action The type of information being requested from the Plugin Install API.
	 * @param object          $args   Plugin API arguments.
	 *
	 * @return object|WP_Error
	 */
	public function add_plugins_to_result( $result, $action, $args ) {
		if ( ! $this->can_add_plugins( $result, $args ) ) {
			return $result;
		}

		$plugins = [
			'seo-by-rank-math' => 'seo-by-rank-math/rank-math.php',
			'imagify'          => 'imagify/imagify.php',
		];

		// grab all slugs from the api results.
		$result_slugs = wp_list_pluck( $result->plugins, 'slug' );

		foreach ( $plugins as $slug => $path ) {
			if ( is_plugin_active( $path ) || is_plugin_active_for_network( $path ) ) {
				continue;
			}

			if ( in_array( $slug, $result_slugs, true ) ) {
				foreach ( $result->plugins as $index => $plugin ) {
					if ( is_object( $plugin ) ) {
						$plugin = (array) $plugin;
					}
					if ( $slug === $plugin['slug'] ) {
						$move = $plugin;
						unset( $result->plugins[ $index ] );
						array_unshift( $result->plugins, $move );
					}
				}
				continue;
			}

			$plugin_data = $this->get_plugin_data( $slug );

			if ( empty( $plugin_data ) ) {
				continue;
			}

			array_unshift( $result->plugins, $plugin_data );
		}

		return $result;
	}

	/**
	 * Checks if we can add plugins to the results
	 *
	 * @param object|WP_error $result Response object or WP_Error.
	 * @param object          $args Plugin API arguments.
	 *
	 * @return bool
	 */
	private function can_add_plugins( $result, $args ) {
		if ( is_wp_error( $result ) ) {
			return false;
		}

		if ( empty( $args->browse ) ) {
			return false;
		}

		if ( 'featured' !== $args->browse && 'recommended' !== $args->browse && 'popular' !== $args->browse ) {
			return false;
		}

		if ( ! isset( $result->info['page'] ) || 1 < $result->info['page'] ) {
			return false;
		}

		return true;
	}

	/**
	 * Returns plugin data
	 *
	 * @param string $slug Plugin slug.
	 *
	 * @return array|object
	 */
	private function get_plugin_data( string $slug ) {
		$query_args = [
			'slug'   => $slug,
			'fields' => [
				'icons'             => true,
				'active_installs'   => true,
				'short_description' => true,
				'group'             => true,
			],
		];

		$plugin_data = plugins_api( 'plugin_information', $query_args );

		if ( is_wp_error( $plugin_data ) ) {
			return [];
		}

		return $plugin_data;
	}
}
