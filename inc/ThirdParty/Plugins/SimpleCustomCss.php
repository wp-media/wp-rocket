<?php
namespace WP_Rocket\ThirdParty\Plugins;

use WP_Rocket\Engine\Optimization\CSSTrait;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for compatibility with Simple Custom CSS plugin.
 *
 * @since  3.6
 * @author Soponar Cristina
 */
class SimpleCustomCss implements Subscriber_Interface {
	use CSSTrait;

	const FILENAME = 'sccss.css';

	/**
	 * Base cache busting folder path
	 *
	 * @var string
	 */
	private $cache_busting_path;

	/**
	 * SCCSS cache busting file path
	 *
	 * @var string
	 */
	private $filepath;

	/**
	 * SCCSS cache busting file URL
	 *
	 * @var string
	 */
	private $file_url;

	/**
	 * Constructor
	 *
	 * @param string $cache_busting_path Base cache busting folder path.
	 * @param string $cache_busting_url  Base cache busting URL.
	 */
	public function __construct( $cache_busting_path, $cache_busting_url ) {
		$blog_id                  = get_current_blog_id();
		$this->cache_busting_path = $cache_busting_path . $blog_id . '/';
		$this->filepath           = $this->cache_busting_path . self::FILENAME;
		$this->file_url           = $cache_busting_url . $blog_id . '/' . self::FILENAME;
	}
	/**
	 * Subscribed events for AMP.
	 *
	 * @since  3.5.3
	 * @author Soponar Cristina
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		if ( ! defined( 'SCCSS_FILE' ) ) {
			return [];
		}

		return [
			'wp_enqueue_scripts'           => [ 'cache_sccss', 98 ],
			'update_option_sccss_settings' => 'update_cache_file',
		];
	}

	/**
	 * Caches SCCSS code & remove the default enqueued URL
	 *
	 * @since 2.9
	 * @author Remy Perona
	 */
	public function cache_sccss() {
		if ( ! rocket_direct_filesystem()->exists( $this->filepath ) ) {
			$this->create_cache_file();
		}

		// This filter is documented in inc/Engine/Optimization/CSS/AbstractCSSOptimization.php.
		wp_enqueue_style( 'scss', apply_filters( 'rocket_css_url', $this->file_url ), '', rocket_direct_filesystem()->mtime( $this->filepath ) );
		remove_action( 'wp_enqueue_scripts', 'sccss_register_style', 99 );
	}

	/**
	 * Deletes & recreates cache for SCCSS code
	 *
	 * @since  3.6
	 * @author Remy Perona
	 */
	public function update_cache_file() {
		rocket_clean_domain();
		$this->create_cache_file();
	}

	/**
	 * Creates cache file for SCCSS code if it does not exist.
	 *
	 * @since 2.9
	 * @author Remy Perona
	 *
	 * @return bool  Returns bool if the files exists or could not be created.
	 */
	private function create_cache_file() {
		$options     = get_option( 'sccss_settings' );
		$raw_content = isset( $options['sccss-content'] ) ? $options['sccss-content'] : '';
		$content     = wp_kses( $raw_content, [ '\'', '\"' ] );
		$content     = str_replace( '&gt;', '>', $content );
		$content     = $this->apply_font_display_swap( $content );

		if ( ! rocket_direct_filesystem()->is_dir( $this->cache_busting_path ) ) {
			rocket_mkdir_p( $this->cache_busting_path );
		}

		return rocket_put_content( $this->filepath, $content );
	}
}
