<?php

namespace WP_Rocket\Engine\CriticalPath;

use WP_Error;
use WP_Filesystem_Direct;

/**
 * Class DataManager
 *
 * @package WP_Rocket\Engine\CriticalPath
 */
class DataManager {

	/**
	 * Base critical CSS path for posts.
	 *
	 * @var string
	 */
	private $critical_css_path;

	/**
	 * Instance of the filesystem handler.
	 *
	 * @var WP_Filesystem_Direct
	 */
	private $filesystem;

	/**
	 * DataManager constructor, adjust the critical css path for posts.
	 *
	 * @param string               $critical_css_path path for main critical css folder.
	 * @param WP_Filesystem_Direct $filesystem        Instance of the filesystem handler.
	 */
	public function __construct( $critical_css_path, $filesystem ) {
		$this->critical_css_path = $critical_css_path . get_current_blog_id() . DIRECTORY_SEPARATOR;
		$this->filesystem        = $filesystem;
	}

	/**
	 * Save CPCSS into file.
	 *
	 * @since 3.6
	 *
	 * @param string $path      Path for cpcss file related to this web page.
	 * @param string $cpcss     CPCSS code to be saved.
	 * @param string $url       URL for item to be used in error messages.
	 * @param bool   $is_mobile If this is cpcss for mobile or not.
	 * @param string $item_type Optional. Type for this item if it's custom or specific type. Default: custom.
	 *
	 * @return bool|WP_Error
	 */
	public function save_cpcss( $path, $cpcss, $url, $is_mobile = false, $item_type = 'custom' ) {
		$file_path_directory = dirname( $this->critical_css_path . $path );

		if ( ! $this->filesystem->is_dir( $file_path_directory ) ) {
			if ( ! rocket_mkdir_p( $file_path_directory ) ) {

				return new WP_Error(
					'cpcss_generation_failed',
					// translators: %s = item URL.
					sprintf(
						$is_mobile
							?
							// translators: %s = item URL.
							__( 'Critical CSS for %1$s on mobile not generated. Error: The API returned an empty response.', 'rocket' )
							:
							// translators: %s = item URL.
							__( 'Critical CSS for %1$s not generated. Error: The API returned an empty response.', 'rocket' ),
						( 'custom' === $item_type ) ? $url : $item_type
						),
					[
						'status' => 400,
					]
				);

			}
		}

		return rocket_put_content(
			$this->critical_css_path . $path,
			wp_strip_all_tags( $cpcss, true )
		);
	}

	/**
	 * Delete critical css file by path.
	 *
	 * @param string $path      Critical css file path to be deleted.
	 * @param bool   $is_mobile If this is cpcss for mobile or not.
	 *
	 * @return bool|WP_Error
	 */
	public function delete_cpcss( $path, $is_mobile = false ) {
		$full_path = $this->critical_css_path . $path;

		if ( ! $this->filesystem->exists( $full_path ) ) {
			return new WP_Error(
				'cpcss_not_exists',
				$is_mobile
					?
					__( 'Critical CSS file for mobile does not exist', 'rocket' )
					:
					__( 'Critical CSS file does not exist', 'rocket' ),
				[
					'status' => 400,
				]
			);
		}

		if ( ! $this->filesystem->delete( $full_path ) ) {
			return new WP_Error(
				'cpcss_deleted_failed',
				$is_mobile
					?
					__( 'Critical CSS file for mobile cannot be deleted', 'rocket' )
					:
					__( 'Critical CSS file cannot be deleted', 'rocket' ),
				[
					'status' => 400,
				]
			);
		}

		return true;
	}

	/**
	 * Get job_id from cache based on item_url.
	 *
	 * @since 3.6
	 *
	 * @param string $item_url  URL for item to be used in error messages.
	 * @param bool   $is_mobile Bool identifier for is_mobile CPCSS generation.
	 *
	 * @return mixed
	 */
	public function get_cache_job_id( $item_url, $is_mobile = false ) {
		$cache_key = $this->get_cache_key_from_url( $item_url, $is_mobile );

		return get_transient( $cache_key );
	}

	/**
	 * Set Job_id for Item_url into cache.
	 *
	 * @since 3.6
	 *
	 * @param string $item_url  URL for item to be used in error messages.
	 * @param string $job_id    ID for the job to get details.
	 * @param bool   $is_mobile Bool identifier for is_mobile CPCSS generation.
	 *
	 * @return bool
	 */
	public function set_cache_job_id( $item_url, $job_id, $is_mobile = false ) {
		$cache_key = $this->get_cache_key_from_url( $item_url, $is_mobile );

		return set_transient( $cache_key, $job_id, HOUR_IN_SECONDS );
	}

	/**
	 * Delete job_id from cache based on item_url.
	 *
	 * @since 3.6
	 *
	 * @param string $item_url  URL for item to be used in error messages.
	 * @param bool   $is_mobile Bool identifier for is_mobile CPCSS generation.
	 *
	 * @return bool
	 */
	public function delete_cache_job_id( $item_url, $is_mobile = false ) {
		$cache_key = $this->get_cache_key_from_url( $item_url, $is_mobile );

		return delete_transient( $cache_key );
	}

	/**
	 * Get cache key from url to be used in caching job_id.
	 *
	 * @since 3.6
	 *
	 * @param string $item_url  URL for item to be used in error messages.
	 * @param bool   $is_mobile Bool identifier for is_mobile CPCSS generation.
	 *
	 * @return string
	 */
	private function get_cache_key_from_url( $item_url, $is_mobile = false ) {
		$encoded_url = md5( $item_url );

		if ( $is_mobile ) {
			$encoded_url .= '_mobile';
		}

		return 'rocket_specific_cpcss_job_' . $encoded_url;
	}
}
