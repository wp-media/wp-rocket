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
			'plugins_api_result'       => [ 'add_rocket_info', 10, 3 ],
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
	public function exclude_rocket_from_wp_info( $bool, $action, $args ) {
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
}
