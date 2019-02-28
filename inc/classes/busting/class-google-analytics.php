<?php
namespace WP_Rocket\Busting;

use WP_Rocket\Logger\Logger;

/**
 * Manages the cache busting of the Google Analytics file.
 *
 * @since  3.1
 * @author Remy Perona
 */
class Google_Analytics extends Abstract_Busting {
	use File_Busting;

	/**
	 * Context used for the logger.
	 *
	 * @var    string
	 * @since  3.2.4
	 * @author Grégory Viguier
	 */
	const LOGGER_CONTEXT = 'gg analytics';

	/**
	 * Google Analytics URL.
	 *
	 * @var    string
	 * @since  3.1
	 * @access protected
	 * @author Remy Perona
	 */
	protected $url = 'https://www.google-analytics.com/analytics.js';

	/**
	 * File name (local).
	 * %s is a "version": a md5 hash of the file contents.
	 *
	 * @var    string
	 * @since  3.2.4
	 * @access protected
	 * @author Grégory Viguier
	 */
	protected $filename = 'ga-%s.js';

	/**
	 * Current file version (local): a md5 hash of the file contents.
	 *
	 * @var    string
	 * @since  3.2.4
	 * @access protected
	 * @author Grégory Viguier
	 */
	protected $file_version;

	/**
	 * Flag to track the replacement.
	 *
	 * @var    bool
	 * @since  3.1
	 * @access protected
	 * @author Remy Perona
	 */
	protected $is_replaced = false;

	/**
	 * Filesystem object.
	 *
	 * @var    object
	 * @since  3.2.4
	 * @access protected
	 * @author Grégory Viguier
	 */
	protected $filesystem = false;

	/**
	 * Constructor.
	 *
	 * @since  3.1
	 * @access public
	 * @author Remy Perona
	 *
	 * @param string $busting_path Path to the busting directory.
	 * @param string $busting_url  URL of the busting directory.
	 */
	public function __construct( $busting_path, $busting_url ) {
		$this->busting_path = $busting_path . 'google-tracking/';
		$this->busting_url  = $busting_url . 'google-tracking/';
		$this->filesystem   = \rocket_direct_filesystem();
	}

	/** ----------------------------------------------------------------------------------------- */
	/** PUBLIC METHODS ========================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Performs the replacement process.
	 *
	 * @since  3.1
	 * @access public
	 * @author Remy Perona
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function replace_url( $html ) {
		$this->is_replaced = false;

		$tag = $this->find( '<script[^>]*?>(.*)<\/script>', $html );

		if ( ! $tag ) {
			return $html;
		}

		Logger::info(
			'GOOGLE ANALYTICS CACHING PROCESS STARTED.',
			[
				self::LOGGER_CONTEXT,
				'tag' => $tag,
			]
		);

		if ( ! $this->save( $this->url ) ) {
			return $html;
		}

		$replace_tag = preg_replace( '/(?:https?:)?\/\/www\.google-analytics\.com\/analytics\.js/i', $this->get_busting_url(), $tag );
		$html        = str_replace( $tag, $replace_tag, $html );

		$this->is_replaced = true;

		Logger::info(
			'Google Analytics caching process succeeded.',
			[
				self::LOGGER_CONTEXT,
				'file' => $this->get_busting_path(),
			]
		);

		return $html;
	}

	/**
	 * Tell if the replacement was sucessful or not.
	 *
	 * @since  3.1
	 * @access public
	 * @author Remy Perona
	 *
	 * @return bool
	 */
	public function is_replaced() {
		return $this->is_replaced;
	}

	/**
	 * Saves the content of the URL to cache to the busting file.
	 *
	 * @since  3.2.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  string $url URL to get the content from.
	 * @return bool
	 */
	public function refresh_save( $url ) {
		// Before doing anything, make sure the busting file can be created.
		if ( ! $this->is_busting_dir_writable() ) {
			return false;
		}

		// Get remote content.
		$content = $this->get_remote_contents( $url );

		if ( ! $content ) {
			// Could not get the remote contents.
			return false;
		}

		$version = \md5( $content );
		$path    = $this->get_busting_file_path( $version );

		return $this->update_file_contents( $path, $content );
	}

	/** ----------------------------------------------------------------------------------------- */
	/** REMOTE FILE ============================================================================= */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Get the Google Analytics URL.
	 *
	 * @since  3.1
	 * @access public
	 * @author Remy Perona
	 *
	 * @return string
	 */
	public function get_url() {
		return $this->url;
	}

	/** ----------------------------------------------------------------------------------------- */
	/** VARIOUS INTERNAL TOOLS ================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Searches for element(s) in the DOM.
	 *
	 * @since  3.1
	 * @access public
	 * @author Remy Perona
	 *
	 * @param string $pattern Pattern to match.
	 * @param string $html    HTML content.
	 * @return string
	 */
	protected function find( $pattern, $html ) {
		\preg_match_all( '/' . $pattern . '/Umsi', $html, $matches, PREG_SET_ORDER );

		if ( ! $matches ) {
			return false;
		}

		$matches = \array_map(
			function( $match ) {
				if ( false === \strpos( $match[1], 'GoogleAnalyticsObject' ) ) {
					return;
				}

				return $match[0];
			},
			$matches
		);

		$matches = \array_values( \array_filter( $matches ) );

		if ( ! $matches ) {
			return false;
		}

		return $matches[0];
	}
}
