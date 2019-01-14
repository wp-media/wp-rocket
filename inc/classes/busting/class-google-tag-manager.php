<?php
namespace WP_Rocket\Busting;

use WP_Rocket\Busting\Google_Analytics;
use WP_Rocket\Logger\Logger;

/**
 * Manages the cache busting of the Google Tag Manager file
 *
 * @since 3.1
 * @author Remy Perona
 */
class Google_Tag_Manager extends Abstract_Busting {
	use File_Busting;

	/**
	 * Context used for the logger.
	 *
	 * @var    string
	 * @since  3.2.4
	 * @author Grégory Viguier
	 */
	const LOGGER_CONTEXT = 'gg tag manager';

	/**
	 * File name (local).
	 * %s is a "version": a md5 hash of the file contents.
	 *
	 * @var    string
	 * @since  3.2.4
	 * @access protected
	 * @author Grégory Viguier
	 */
	protected $filename = 'gtm-%s.js';

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
	 * Filesystem object.
	 *
	 * @var    object
	 * @since  3.2.4
	 * @access protected
	 * @author Grégory Viguier
	 */
	protected $filesystem = false;

	/**
	 * Google_Analytics object.
	 *
	 * @var    object
	 * @since  3.2.4
	 * @access protected
	 * @author Grégory Viguier
	 */
	protected $ga_busting = false;

	/**
	 * Constructor.
	 *
	 * @since  3.1
	 * @access public
	 * @author Remy Perona
	 *
	 * @param string           $busting_path Path to the busting directory.
	 * @param string           $busting_url  URL of the busting directory.
	 * @param Google_Analytics $ga_busting   A Google_Analytics instance.
	 */
	public function __construct( $busting_path, $busting_url, Google_Analytics $ga_busting ) {
		$blog_id            = get_current_blog_id();
		$this->busting_path = $busting_path . $blog_id . '/';
		$this->busting_url  = $busting_url . $blog_id . '/';
		$this->ga_busting   = $ga_busting;
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
		$script = $this->find( '<script(\s+[^>]+)?\s+src\s*=\s*[\'"]\s*?((?:https?:)?\/\/www\.googletagmanager\.com(?:.+)?)\s*?[\'"]([^>]+)?\/?>', $html );

		if ( ! $script ) {
			return $html;
		}

		Logger::info(
			'GOOGLE TAG MANAGER CACHING PROCESS STARTED.',
			[
				self::LOGGER_CONTEXT,
				'tag' => $script,
			]
		);

		if ( ! $this->save( $script[2] ) ) {
			return $html;
		}

		$replace_script = str_replace( $script[2], $this->get_busting_url(), $script[0] );
		$replace_script = str_replace( '<script', '<script data-no-minify="1"', $replace_script );
		$html           = str_replace( $script[0], $replace_script, $html );

		Logger::info(
			'Google Tag Manager caching process succeeded.',
			[
				self::LOGGER_CONTEXT,
				'file' => $this->get_busting_path(),
			]
		);

		return $html;
	}

	/**
	 * Saves the content of the URL to cache to the busting file.
	 *
	 * @since  3.2
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
		$content = $this->replace_ga_url( $content );

		return $this->update_file_contents( $path, $content );
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
		preg_match_all( '/' . $pattern . '/Umsi', $html, $matches, PREG_SET_ORDER );

		if ( empty( $matches ) ) {
			return false;
		}

		return $matches[0];
	}

	/**
	 * Replaces the Google Analytics URL by the local copy inside the gtm-local.js file content
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $content JavaScript content.
	 * @return string
	 */
	protected function replace_ga_url( $content ) {
		if ( ! $this->ga_busting->save( $this->ga_busting->get_url() ) ) {
			return $content;
		}

		return str_replace( $this->ga_busting->get_url(), $this->ga_busting->get_busting_url(), $content );
	}
}
