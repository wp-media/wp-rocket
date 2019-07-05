<?php
namespace WP_Rocket\Subscriber\Plugin;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Manages the plugin information.
 *
 * @since  3.3.6
 * @author Grégory Viguier
 */
class Information_Subscriber implements Subscriber_Interface {
	use \WP_Rocket\Traits\Updater_Api_Tools;

	/**
	 * Plugin slug.
	 *
	 * @var    string
	 * @since  3.3.6
	 * @access private
	 * @author Grégory Viguier
	 */
	private $plugin_slug;

	/**
	 * URL to contact to get plugin info.
	 *
	 * @var    string
	 * @since  3.3.6
	 * @access private
	 * @author Grégory Viguier
	 */
	private $api_url;

	/**
	 * An ID to use when a API request fails.
	 *
	 * @var    string
	 * @since  3.3.6
	 * @access protected
	 * @author Grégory Viguier
	 */
	protected $request_error_id = 'plugins_api_failed';

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
			'plugins_api'        => [ 'exclude_rocket_from_wp_info', 10, 3 ],
			'plugins_api_result' => [ 'add_rocket_info', 10, 3 ],
		];
	}

	/** ----------------------------------------------------------------------------------------- */
	/** PLUGIN INFO ============================================================================= */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Don’t ask for plugin info to the repository.
	 *
	 * @since  3.3.6
	 * @access public
	 * @see    plugins_api()
	 * @author Grégory Viguier
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
	 * @since  3.3.6
	 * @access public
	 * @see    plugins_api()
	 * @author Grégory Viguier
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

		$request = wp_remote_post(
			$this->api_url,
			[
				'timeout' => 30,
				'action'  => 'plugin_information',
				'request' => maybe_serialize( $args ),
			]
		);

		if ( is_wp_error( $request ) ) {
			return $this->get_request_error( $request->get_error_message() );
		}

		$res  = maybe_unserialize( wp_remote_retrieve_body( $request ) );
		$code = wp_remote_retrieve_response_code( $request );

		if ( 200 !== $code || ! ( is_object( $res ) || is_array( $res ) ) ) {
			return $this->get_request_error( wp_remote_retrieve_body( $request ) );
		}

		return $res;
	}

	/** ----------------------------------------------------------------------------------------- */
	/** TOOLS =================================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Tell if requesting WP Rocket plugin info.
	 *
	 * @since  3.3.6
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @param  string $action The type of information being requested from the Plugin Install API.
	 * @param  object $args   Plugin API arguments.
	 * @return bool
	 */
	private function is_requesting_rocket_info( $action, $args ) {
		return ( 'query_plugins' === $action || 'plugin_information' === $action ) && isset( $args->slug ) && $args->slug === $this->plugin_slug;
	}
}
