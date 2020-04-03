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
		$events['update_option_sccss_settings'] = 'update_cache_file';

		return $events;
	}

	/**
	 * Caches SCCSS code & remove the default enqueued URL
	 *
	 * @since 2.9
	 * @author Remy Perona
	 */
	public function cache_sccss() {
		$sccss = $this->get_cache_busting_paths( 'sccss.css' );

		if ( ! $this->create_cache_file( $sccss['bustingpath'], $sccss['filepath'], false ) ) {
			return;
		}

		wp_enqueue_style( 'scss', $sccss['url'], '', rocket_direct_filesystem()->mtime( $sccss['filepath'] ) );
		remove_action( 'wp_enqueue_scripts', 'sccss_register_style', 99 );
	}

	/**
	 * Deletes & recreates cache for SCCSS code
	 *
	 * @since 2.9
	 * @author Remy Perona
	 */
	public function update_cache_file() {
		$sccss = $this->get_cache_busting_paths( 'sccss.css' );
		rocket_clean_domain();
		$this->create_cache_file( $sccss['bustingpath'], $sccss['filepath'], true );
	}

	/**
	 * Creates cache file for SCCSS code if it does not exist.
	 *
	 * @since 2.9
	 * @author Remy Perona
	 *
	 * @param string $cache_busting_path   Path to the cache busting directory.
	 * @param string $cache_sccss_filepath Path to the sccss cache file.
	 * @param bool   $allow_update         Allow to update the file.
	 *
	 * @return bool  Returns bool if the files exists or could not be created.
	 */
	private function create_cache_file( $cache_busting_path, $cache_sccss_filepath, $allow_update ) {
		$filesystem = rocket_direct_filesystem();
		// File exists. Do not recreate it.
		if ( $filesystem->exists( $cache_sccss_filepath ) && ! $allow_update ) {
			return true;
		}

		$options     = get_option( 'sccss_settings' );
		$raw_content = isset( $options['sccss-content'] ) ? $options['sccss-content'] : '';
		$content     = wp_kses( $raw_content, [ '\'', '\"' ] );
		$content     = str_replace( '&gt;', '>', $content );

		if ( ! $filesystem->is_dir( $cache_busting_path ) ) {
			rocket_mkdir_p( $cache_busting_path );
		}

		return rocket_put_content( $cache_sccss_filepath, $content );
	}

	/**
	 * Returns paths used for cache busting
	 *
	 * @since 2.9
	 * @author Remy Perona
	 *
	 * @param string $filename name of the cache busting file.
	 * @return array Array of paths used for cache busting
	 */
	private function get_cache_busting_paths( $filename ) {
		$blog_id                = get_current_blog_id();
		$cache_busting_path     = rocket_get_constant( 'WP_ROCKET_CACHE_BUSTING_PATH' ) . $blog_id;
		$filename               = rocket_realpath( rtrim( str_replace( [ ' ', '%20' ], '-', $filename ) ) );
		$cache_busting_filepath = $cache_busting_path . $filename;
		$cache_busting_url      = rocket_get_constant( 'WP_ROCKET_CACHE_BUSTING_URL' ) . $blog_id . $filename;

		/** This filter is documented in inc/functions/minify.php */
		$cache_busting_url = apply_filters( 'rocket_css_url', $cache_busting_url );

		return [
			'bustingpath' => $cache_busting_path,
			'filepath'    => $cache_busting_filepath,
			'url'         => $cache_busting_url,
		];
	}
}
