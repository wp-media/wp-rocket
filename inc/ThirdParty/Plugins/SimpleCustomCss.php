<?php
namespace WP_Rocket\ThirdParty\Plugins;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for compatibility with Simple Custom CSS plugin.
 *
 * @since  3.6
 * @author Soponar Cristina
 */
class SimpleCustomCss implements Subscriber_Interface {
	/**
	 * Cache Busting folder path
	 *
	 * @var string
	 */
	private $cache_busting_path;

	/**
	 * File path
	 *
	 * @var string
	 */
	private $filepath;

	/**
	 * File URL
	 *
	 * @var string
	 */
	private $cache_busting_url;

	/**
	 * Constructor
	 */
	public function __construct() {
		$blog_id                  = get_current_blog_id();
		$this->cache_busting_path = rocket_get_constant( 'WP_ROCKET_CACHE_BUSTING_PATH' ) . $blog_id . '/';
		$this->filepath           = $this->cache_busting_path . 'sccss.css';
		$cache_busting_url        = rocket_get_constant( 'WP_ROCKET_CACHE_BUSTING_URL' ) . $blog_id . 'sccss.css';

		/** This filter is documented in inc/functions/minify.php */
		$this->cache_busting_url = apply_filters( 'rocket_css_url', $cache_busting_url );
	}
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
		if ( ! $this->create_cache_file( false ) ) {
			return;
		}

		wp_enqueue_style( 'scss', $this->cache_busting_url, '', rocket_direct_filesystem()->mtime( $this->filepath ) );
		remove_action( 'wp_enqueue_scripts', 'sccss_register_style', 99 );
	}

	/**
	 * Deletes & recreates cache for SCCSS code
	 *
	 * @since 2.9
	 * @author Remy Perona
	 */
	public function update_cache_file() {
		rocket_clean_domain();
		$this->create_cache_file( true );
	}

	/**
	 * Creates cache file for SCCSS code if it does not exist.
	 *
	 * @since 2.9
	 * @author Remy Perona
	 *
	 * @param bool $allow_update Allow to update the file.
	 *
	 * @return bool  Returns bool if the files exists or could not be created.
	 */
	private function create_cache_file( $allow_update ) {
		$filesystem = rocket_direct_filesystem();
		// File exists. Do not recreate it.
		if ( $filesystem->exists( $this->filepath ) && ! $allow_update ) {
			return true;
		}

		$options     = get_option( 'sccss_settings' );
		$raw_content = isset( $options['sccss-content'] ) ? $options['sccss-content'] : '';
		$content     = wp_kses( $raw_content, [ '\'', '\"' ] );
		$content     = str_replace( '&gt;', '>', $content );

		if ( ! $filesystem->is_dir( $this->cache_busting_path ) ) {
			rocket_mkdir_p( $this->cache_busting_path );
		}

		return rocket_put_content( $this->filepath, $content );
	}
}
