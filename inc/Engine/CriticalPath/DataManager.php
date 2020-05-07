<?php
namespace WP_Rocket\Engine\CriticalPath;

use WP_Error;

/**
 * Class DataManager
 *
 * @package WP_Rocket\Engine\CriticalPath
 */
class DataManager {

	/**
	 * APIClient object.
	 *
	 * @var APIClient
	 */
	private $api_client;

	/**
	 * Base critical CSS path for posts.
	 *
	 * @var string
	 */
	private $critical_css_path;

	/**
	 * DataManager constructor, adjust the critical css path for posts.
	 *
	 * @param APIClient $api_client api_client to call API for cpcss endpoint.
	 * @param string    $critical_css_path path for main critical css folder.
	 */
	public function __construct( APIClient $api_client, $critical_css_path ) {
		$this->critical_css_path = $critical_css_path . get_current_blog_id() . '/posts/';
		$this->api_client        = $api_client;
	}

	/**
	 * Delete critical css file by path.
	 *
	 * @param string $path critical css file path to be deleted.
	 * @return bool|WP_Error
	 */
	public function delete_cpcss( $path ) {
		$filesystem = rocket_direct_filesystem();

		if ( ! $filesystem->exists( $path ) ) {
			return new WP_Error(
				'cpcss_path_not_found',
				__( 'Critical CSS file does not exist', 'rocket' ),
				[
					'status' => 400,
				]
			);
		}

		if ( ! $filesystem->delete( $path ) ) {
			return new WP_Error(
				'cpcss_deleted_failed',
				__( 'Critical CSS file cannot be deleted', 'rocket' ),
				[
					'status' => 400,
				]
			);
		}

		return true;
	}

}
