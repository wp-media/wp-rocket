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
	 * Filename for the cache busting file.
	 *
	 * @var string
	 */
	protected $filename;

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
	 * @param string $url URL to get the content from.
	 * @return bool
	 */
	public function save( $url ) {
		$path = $this->busting_path . $this->filename;

		if ( \rocket_direct_filesystem()->exists( $path ) ) {
			return true;
		}

		return $this->refresh_save( $url );
	}

	/**
	 * Saves the content of the URL to bust to the busting file.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  string $url URL to get the content from.
	 * @return bool
	 */
	public function refresh_save( $url ) {
		$path    = $this->busting_path . $this->filename;
		$content = $this->get_file_content( $url );

		if ( ! $content ) {
			// If a previous version is present, it is kept in place.
			return false;
		}

		if ( ! \rocket_direct_filesystem()->exists( $this->busting_path ) ) {
			\rocket_mkdir_p( $this->busting_path );
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
		return apply_filters( 'rocket_js_url', get_rocket_cdn_url( $this->busting_url . $this->filename, array( 'all', 'css_and_js', 'js' ) ) );
	}

	/**
	 * Performs the replacement process.
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	abstract public function replace_url( $html );

	/**
	 * Searches for element(s) in the DOM
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $pattern Pattern to match.
	 * @param string $html    HTML content.
	 * @return string
	 */
	abstract protected function find( $pattern, $html );
}
