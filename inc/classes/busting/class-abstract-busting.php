<?php
namespace WP_Rocket\Busting;

/**
 * Abstract class for assets busting
 *
 * @since 3.1
 * @author Remy Perona
 */
abstract class Abstract_Busting {
	/**
	 * Cache busting files base path
	 *
	 * @var string
	 */
	protected $busting_path;

	/**
	 * Cache busting base URL
	 *
	 * @var string
	 */
	protected $busting_url;

	/**
	 * Flag to track the replacement
	 *
	 * @var bool
	 */
	protected $is_replaced;

	/**
	 * Filename for the cache busting file.
	 *
	 * @var string
	 */
	protected $filename;

	/**
	 * Constructor
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $busting_path Cache busting files base path.
	 * @param string $busting_url  Cache busting base URL.
	 */
	public function __construct( $busting_path, $busting_url ) {
		$blog_id            = get_current_blog_id();
		$this->busting_path = $busting_path . $blog_id . '/';
		$this->busting_url  = $busting_url . $blog_id . '/';
		$this->is_replaced  = false;
	}

	/**
	 * Gets the content of an URL
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $url The URL to request.
	 * @return string|bool
	 */
	protected function get_file_content( $url ) {
		$content = wp_remote_retrieve_body( wp_remote_get( $url ) );

		if ( ! $content ) {
			return false;
		}

		return $content;
	}

	/**
	 * Saves the content of the URL to bust to the busting file if it doesn't exist yet.
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $url      URL to get the content from.
	 * @param string $filename Filename to use for the file.
	 * @return bool
	 */
	public function save( $url, $filename ) {
		$path = $this->busting_path . $filename . '.js';

		if ( \rocket_direct_filesystem()->exists( $path ) ) {
			return true;
		}

		$content = $this->get_file_content( $url );

		if ( ! $content ) {
			return false;
		}

		if ( ! \rocket_put_content( $path, $content ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Gets the final URL for the cache busting file.
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return string
	 */
	protected function get_busting_url() {
		// This filter is documented in inc/functions/minify.php.
		return apply_filters( 'rocket_js_url', get_rocket_cdn_url( $this->busting_url . $this->filename . '.js', array( 'all', 'css_and_js', 'js' ) ) );
	}

	/**
	 * Performs the replacement process.
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param HtmlPageCrawler $crawler Instance of HtmlPageCrawler class.
	 * @return string
	 */
	abstract public function replace_url( $crawler );

	/**
	 * Searches for element(s) in the DOM
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param HtmlPageCrawler $crawler Instance of HtmlPageCrawler class.
	 * @return string
	 */
	abstract protected function find( $crawler );

	/**
	 * Replaces element(s) in the DOM and return the final HTML
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param HtmlPageCrawler $node Instance of HtmlPageCrawler class.
	 * @return string
	 */
	abstract protected function replace( $node );
}
