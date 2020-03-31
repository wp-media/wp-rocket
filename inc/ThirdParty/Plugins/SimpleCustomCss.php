<?php
namespace WP_Rocket\ThirdParty\Plugins;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for compatibility with Simple Custom CSS plugin.
 *
 * @since  3.5.3
 * @author Soponar Cristina
 */
class SimpleCustomCss implements Subscriber_Interface {
	/**
	 * Subscribed events for AMP.
	 *
	 * @since  3.5.3
	 * @author Soponar Cristina
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		$events = [];

		if ( ! defined( 'SCCSS_FILE' ) ) {
			return $events;
		}

		$events['wp_enqueue_scripts']           = [ 'cache_sccss', 98 ];
		$events['update_option_sccss_settings'] = 'delete_cache_file';

		return $events;
	}

	/**
	 * Caches SCCSS code & remove the default enqueued URL
	 *
	 * @since 2.9
	 * @author Remy Perona
	 */
	public function cache_sccss() {
		$sccss = rocket_get_cache_busting_paths( 'sccss.css', 'css' );

		if ( ! file_exists( $sccss['filepath'] ) ) {
			$this->create_cache_file( $sccss['bustingpath'], $sccss['filepath'] );
		}

		// Bailout if the SCCSS file could not be created.
		if ( ! file_exists( $sccss['filepath'] ) ) {
			return;
		}

		wp_enqueue_style( 'scss', $sccss['url'], '', filemtime( $sccss['filepath'] ) );
		remove_action( 'wp_enqueue_scripts', 'sccss_register_style', 99 );
	}

	/**
	 * Deletes & recreates cache for SCCSS code
	 *
	 * @since 2.9
	 * @author Remy Perona
	 */
	public function delete_cache_file() {
		$sccss = rocket_get_cache_busting_paths( 'sccss.css', 'css' );

		array_map( 'unlink', glob( $sccss['bustingpath'] . 'sccss*.css' ) );
		rocket_clean_domain();
		$this->create_cache_file( $sccss['bustingpath'], $sccss['filepath'] );
	}

	/**
	 * Creates the cache file for SCCSS code
	 *
	 * @since 2.9
	 * @author Remy Perona
	 *
	 * @param string $cache_busting_path Path to the cache busting directory.
	 * @param string $cache_sccss_filepath Path to the sccss cache file.
	 */
	public function create_cache_file( $cache_busting_path, $cache_sccss_filepath ) {
		$options     = get_option( 'sccss_settings' );
		$raw_content = isset( $options['sccss-content'] ) ? $options['sccss-content'] : '';
		$content     = wp_kses( $raw_content, [ '\'', '\"' ] );
		$content     = str_replace( '&gt;', '>', $content );

		if ( ! rocket_direct_filesystem()->is_dir( $cache_busting_path ) ) {
			rocket_mkdir_p( $cache_busting_path );
		}

		rocket_put_content( $cache_sccss_filepath, $content );
	}
}
