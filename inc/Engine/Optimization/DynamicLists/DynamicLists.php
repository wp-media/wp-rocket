<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\Optimization\DynamicLists;

use WP_Rocket\Abstract_Render;

class DynamicLists {
	use DataManagerTrait;

	/**
	 * APIClient instance
	 *
	 * @var APIClient
	 */
	private $api;

	const ROUTE_NAMESPACE = 'wp-rocket/v1';

	/**
	 * Instantiate the class.
	 *
	 * @param APIClient $api APIClient instance.
	 */
	public function __construct( APIClient $api ) {
		$this->api = $api;
	}

	public function register_rest_route() {
		register_rest_route(
			self::ROUTE_NAMESPACE,
			'wpr-dynamic-lists',
			[
				'methods'             => 'PUT',
				'callback'            => [ $this, 'rest_update_response' ],
				//'permission_callback' => current_user_can( 'rocket_manage_options' ),
			]
		);
	}

	/**
	 * Enable CDN and add RocketCDN URL to WP Rocket options
	 *
	 * @param \WP_REST_Request $request the WP REST Request object.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function rest_update_response( \WP_REST_Request $request ) {
		$response = $this->update_lists_from_remote();
		return rest_ensure_response( $response );
	}

	public function update_lists_from_remote() {
		$hash   = $this->get_lists_from_file() ? md5( $this->get_lists_from_file() ) : '';
		$result = $this->api->get_exclusions_list( $hash );
		var_dump($result);
		if ( 200 !== $result['code'] || empty( $result['body'] ) ) {
			return [
				'success' => false,
				'data'    => '',
			];
		}
		if ( ! $this->put_lists_to_file( $result['body'] ) ) {
			return [
				'success' => false,
				'data'    => '',
			];
		}

		return [
			'success' => true,
			'data'    => $result['body'],
		];
	}

	/*public function display_resync_lists_section() {
		echo $this->generate( 'settings/resync-lists', [] );
	}*/
	public function update_lists_after_upgrade() {
		$this->update_lists_from_remote();
	}
}
