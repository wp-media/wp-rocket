<?php
namespace WP_Rocket\Busting;

use WP_Rocket\Logger\Logger;

/**
 * Manages the cache busting of the Facebook SDK file.
 *
 * @since  3.2
 * @author Grégory Viguier
 */
class Facebook_SDK extends Abstract_Busting {

	/**
	 * Facebook SDK URL.
	 * %s is a locale like "en_US".
	 *
	 * @var    string
	 * @since  3.2
	 * @access protected
	 * @author Grégory Viguier
	 */
	protected $url = 'https://connect.facebook.net/%s/sdk.js';

	/**
	 * Filename for the cache busting file.
	 * %s is a locale like "en_US".
	 *
	 * @var    string
	 * @since  3.2
	 * @access protected
	 * @author Grégory Viguier
	 */
	protected $filename = 'fbsdk-%s.js';

	/**
	 * Flag to track the replacement.
	 *
	 * @var    bool
	 * @since  3.2
	 * @access private
	 * @author Grégory Viguier
	 */
	protected $is_replaced = false;

	/**
	 * Constructor.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param string $busting_path Path to the busting directory.
	 * @param string $busting_url  URL of the busting directory.
	 */
	public function __construct( $busting_path, $busting_url ) {
		/** Warning: the file name and script URL are dynamic, and must be run through sprintf(). */
		$this->busting_path = $busting_path . 'facebook-tracking/';
		$this->busting_url  = $busting_url . 'facebook-tracking/';
	}

	/**
	 * Perform the URL replacement process.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  string $html HTML contents.
	 * @return string       HTML contents.
	 */
	public function replace_url( $html ) {
		$this->is_replaced = false;

		$tag = $this->find( '<script[^>]*?>(.*)<\/script>', $html );

		if ( ! $tag ) {
			return $html;
		}

		Logger::info(
			'FACEBOOK SDK CACHING PROCESS STARTED.',
			[
				'fb sdk',
				'tag' => $tag,
			]
		);

		$locale     = $this->get_locale_from_url( $tag );
		$remote_url = $this->get_url( $locale );

		if ( ! $this->save( $remote_url ) ) {
			return $html;
		}

		$file_url    = $this->get_busting_file_url( $locale );
		$replace_tag = preg_replace( '@(?:https?:)?//connect\.facebook\.net/[a-zA-Z_-]+/sdk\.js@i', $file_url, $tag, -1, $count );

		if ( ! $count || false === strpos( $html, $tag ) ) {
			Logger::error( 'The local file URL could not be replaced in the page contents.', [ 'fb sdk' ] );
			return $html;
		}

		$html        = str_replace( $tag, $replace_tag, $html );
		$file_path   = $this->get_busting_file_path( $locale );
		$xfbml       = $this->get_xfbml_from_url( $tag ); // Default value should be set to false.
		$app_id      = $this->get_appId_from_url( $tag ); // APP_ID is the only required value.
		$url_version = $this->get_version_from_url( $tag );
		$version     = false === $url_version ? 'v5.0' : $url_version; // If version is not available set it to the latest: v.5.0.

		if ( false !== $app_id ) {
			// Add FB async init.
			$fb_async_script = '<script>window.fbAsyncInit = function fbAsyncInit () {FB.init({appId: \'' . $app_id . '\',xfbml: ' . $xfbml . ',version: \'' . $version . '\'})}</script>';
			$html            = str_replace( '</body>', $fb_async_script . '</body>', $html );
		}

		$this->is_replaced = true;

		/**
		 * Triggered once the Facebook SDK URL has been replaced in the page contents.
		 *
		 * @since  3.2
		 * @author Grégory Viguier
		 *
		 * @param string $file_url  URL of the local main file.
		 * @param string $file_path Path to the local file.
		 */
		do_action( 'rocket_after_facebook_sdk_url_replaced', $file_url, $file_path );

		Logger::info(
			'Facebook SDK caching process succeeded.',
			[
				'fb sdk',
				'file' => $file_path,
			]
		);

		return $html;
	}

	/**
	 * Tell if the replacement was sucessful or not.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function is_replaced() {
		return $this->is_replaced;
	}

	/** ----------------------------------------------------------------------------------------- */
	/** GRAB/MANIPULATE DATA IN CONTENTS ======================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Search for an element in the DOM.
	 *
	 * @since  3.2
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @param  string $pattern Pattern to match.
	 * @param  string $html    HTML contents.
	 * @return string|bool     The matched HTML on success. False if nothing is found.
	 */
	protected function find( $pattern, $html ) {
		preg_match_all( '/' . $pattern . '/Umsi', $html, $matches, PREG_SET_ORDER );

		if ( empty( $matches ) ) {
			return false;
		}

		foreach ( $matches as $match ) {
			if ( trim( $match[1] ) && preg_match( '@//connect\.facebook\.net/[a-zA-Z_-]+/sdk\.js@i', $match[1] ) ) {
				return $match[0];
			}
		}

		return false;
	}

	/** ----------------------------------------------------------------------------------------- */
	/** UPDATE/SAVE A LOCAL FILE ================================================================ */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Save the contents of a URL into a local file if it doesn't exist yet.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  string $url URL to get the contents from.
	 * @return bool        True on success. False on failure.
	 */
	public function save( $url ) {
		$locale = $this->get_locale_from_url( $url );
		$path   = $this->get_busting_file_path( $locale );

		if ( \rocket_direct_filesystem()->exists( $path ) ) {
			// If a previous version is present, keep it.
			return true;
		}

		return $this->refresh_save( $url );
	}

	/**
	 * Save the contents of a URL into a local file.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  string $url URL to get the contents from.
	 * @return bool        True on success. False on failure.
	 */
	public function refresh_save( $url ) {
		$content = $this->get_file_content( $url );

		if ( ! $content ) {
			// Error, we couldn't fetch the file contents.
			return false;
		}

		$locale = $this->get_locale_from_url( $url );
		$path   = $this->get_busting_file_path( $locale );

		return (bool) $this->update_file_contents( $path, $content );
	}

	/**
	 * Add new contents to a file. If the file doesn't exist, it is created.
	 *
	 * @since  3.2
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @param  string $file_path     Path to the file to update.
	 * @param  string $file_contents New contents.
	 * @return string|bool           The file contents on success. False on failure.
	 */
	private function update_file_contents( $file_path, $file_contents ) {
		if ( ! \rocket_direct_filesystem()->exists( $this->busting_path ) ) {
			\rocket_mkdir_p( $this->busting_path );
		}

		if ( ! \rocket_put_content( $file_path, $file_contents ) ) {
			Logger::error(
				'Contents could not be written into file.',
				[
					'fb sdk',
					'path' => $file_path,
				]
			);
			return false;
		}

		/**
		 * Triggered once a file contents have been updated.
		 *
		 * @since  3.2
		 * @author Grégory Viguier
		 *
		 * @param  string $file_path     Path to the file to update.
		 * @param  string $file_contents The file contents.
		 */
		do_action( 'rocket_after_facebook_sdk_file_updated', $file_path, $file_contents );

		return $file_contents;
	}

	/** ----------------------------------------------------------------------------------------- */
	/** PUBLIC BULK ACTIONS ON LOCAL FILES ====================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Look for existing local files and update their contents if there's a new version available.
	 * Actually, if a more recent version exists on the FB side, it will delete all local files and hit the home page to recreate them.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return bool True on success. False on failure.
	 */
	public function refresh() {
		$files = $this->get_files();

		if ( ! $files ) {
			// No files (or there's an error).
			return false !== $files;
		}

		$error_paths = [];
		$pattern     = $this->escape_file_name( $this->filename );
		$pattern     = sprintf( $pattern, '(?<locale>[a-zA-Z_-]+)' );

		foreach ( $files as $file ) {
			preg_match( '/^' . $pattern . '$/', $file, $matches );

			$remote_url = $this->get_url( $matches['locale'] );

			if ( ! $this->refresh_save( $remote_url ) ) {
				$error_paths[] = $this->get_busting_file_path( $matches['locale'] );
			}
		}

		if ( $error_paths ) {
			Logger::error(
				'Local file(s) could not be updated.',
				[
					'fb sdk',
					'paths' => $error_paths,
				]
			);
		}

		/**
		 * Triggered once all local files have been updated (or not).
		 *
		 * @since  3.2
		 * @author Grégory Viguier
		 *
		 * @param array $files       An array of file names.
		 * @param array $error_paths Paths to the files that couldn't be updated. An empty array if everything is fine.
		 */
		do_action( 'rocket_after_facebook_sdk_files_refresh', $files, $error_paths );

		return ! $error_paths;
	}

	/**
	 * Delete all Facebook SDK busting files.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return bool True on success. False on failure.
	 */
	public function delete() {
		$filesystem = \rocket_direct_filesystem();
		$files      = $this->get_files();

		if ( ! $files ) {
			// No files (or there's an error).
			return false !== $files;
		}

		$error_paths = [];

		foreach ( $files as $file_name ) {
			if ( ! $filesystem->delete( $this->busting_path . $file_name, false, 'f' ) ) {
				$error_paths[] = $this->busting_path . $file_name;
			}
		}

		if ( $error_paths ) {
			Logger::error(
				'Local file(s) could not be deleted.',
				[
					'fb sdk',
					'paths' => $error_paths,
				]
			);
		}

		/**
		 * Triggered once all local files have been deleted (or not).
		 *
		 * @since  3.2
		 * @author Grégory Viguier
		 *
		 * @param array $files       An array of file names.
		 * @param array $error_paths Paths to the files that couldn't be deleted. An empty array if everything is fine.
		 */
		do_action( 'rocket_after_facebook_sdk_files_deleted', $files, $error_paths );

		return ! $error_paths;
	}

	/** ----------------------------------------------------------------------------------------- */
	/** SCAN FOR LOCAL FILES ==================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Get all cached files in the directory.
	 *
	 * @since  3.2
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @return array|bool A list of file names. False on failure.
	 */
	private function get_files() {
		$filesystem = \rocket_direct_filesystem();
		$dir_path   = rtrim( $this->busting_path, '\\/' );

		if ( ! $filesystem->exists( $dir_path ) ) {
			return [];
		}

		if ( ! $filesystem->is_writable( $dir_path ) ) {
			Logger::error(
				'Directory is not writable.',
				[
					'fb sdk',
					'path' => $dir_path,
				]
			);
			return false;
		}

		$dir = $filesystem->dirlist( $dir_path );

		if ( false === $dir ) {
			Logger::error(
				'Could not get the directory contents.',
				[
					'fb sdk',
					'path' => $dir_path,
				]
			);
			return false;
		}

		if ( ! $dir ) {
			return [];
		}

		$list    = [];
		$pattern = $this->escape_file_name( $this->filename );
		$pattern = sprintf( $pattern, '[a-zA-Z_-]+' );

		foreach ( $dir as $entry ) {
			if ( 'f' !== $entry['type'] ) {
				continue;
			}
			if ( preg_match( '/^' . $pattern . '$/', $entry['name'], $matches ) ) {
				$list[ $entry['name'] ] = $entry['name'];
			}
		}

		return $list;
	}

	/** ----------------------------------------------------------------------------------------- */
	/** REMOTE SDK FILE ========================================================================= */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Get the remote Facebook SDK URL.
	 *
	 * @since  3.2
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @param  string $locale A locale string, like 'en_US'.
	 * @return string
	 */
	public function get_url( $locale ) {
		return sprintf( $this->url, $locale );
	}

	/**
	 * Extract the locale from a URL to bust.
	 *
	 * @since  3.2
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @param  string $url Any string containing the URL to bust.
	 * @return string|bool The locale on success. False on failure.
	 */
	private function get_locale_from_url( $url ) {
		$pattern = '@//connect\.facebook\.net/(?<locale>[a-zA-Z_-]+)/sdk\.js@i';

		if ( ! preg_match( $pattern, $url, $matches ) ) {
			return false;
		}

		return $matches['locale'];
	}

	/**
	 * Extract XFBML from a URL to bust.
	 *
	 * @since  3.4.3
	 * @access private
	 * @author Soponar Cristina
	 *
	 * @param  string $url Any string containing the URL to bust.
	 * @return string|bool The XFBML on success. False on failure.
	 */
	private function get_xfbml_from_url( $url ) {
		$pattern = '@//connect\.facebook\.net/(?<locale>[a-zA-Z_-]+)/sdk\.js#(?:.+&)?xfbml=(?<xfbml>[0-9]+)@i';

		if ( ! preg_match( $pattern, $url, $matches ) ) {
			return false;
		}

		return $matches['xfbml'];
	}

	/**
	 * Extract appId from a URL to bust.
	 *
	 * @since  3.4.3
	 * @access private
	 * @author Soponar Cristina
	 *
	 * @param  string $url Any string containing the URL to bust.
	 * @return string|bool The appId on success. False on failure.
	 */
	private function get_appId_from_url( $url ) {
		$pattern = '@//connect\.facebook\.net/(?<locale>[a-zA-Z_-]+)/sdk\.js#(?:.+&)?appId=(?<appId>[0-9]+)@i';

		if ( ! preg_match( $pattern, $url, $matches ) ) {
			return false;
		}

		return $matches['appId'];
	}

	/**
	 * Extract version from a URL to bust.
	 *
	 * @since  3.4.3
	 * @access private
	 * @author Soponar Cristina
	 *
	 * @param  string $url Any string containing the URL to bust.
	 * @return string|bool The version on success. False on failure.
	 */
	private function get_version_from_url( $url ) {
		$pattern = '@//connect\.facebook\.net/(?<locale>[a-zA-Z_-]+)/sdk\.js#(?:.+&)?version=(?<version>[a-zA-Z0-9.]+)@i';

		if ( ! preg_match( $pattern, $url, $matches ) ) {
			return false;
		}

		return $matches['version'];
	}

	/** ----------------------------------------------------------------------------------------- */
	/** BUSTING FILE ============================================================================ */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Get the local Facebook SDK URL.
	 *
	 * @since  3.2
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @param  string $locale A locale string, like 'en_US'.
	 * @return string
	 */
	private function get_busting_file_url( $locale ) {
		$filename = $this->get_busting_file_name( $locale );

		// This filter is documented in inc/functions/minify.php.
		return apply_filters( 'rocket_js_url', apply_filters( 'rocket_facebook_sdk_url', $this->busting_url . $filename ) );
	}

	/**
	 * Get the local Facebook SDK file name.
	 *
	 * @since  3.2
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @param  string $locale A locale string, like 'en_US'.
	 * @return string
	 */
	private function get_busting_file_name( $locale ) {
		return sprintf( $this->filename, $locale );
	}

	/**
	 * Get the local Facebook SDK file path.
	 *
	 * @since  3.2
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @param  string $locale  A locale string, like 'en_US'.
	 * @return string
	 */
	private function get_busting_file_path( $locale ) {
		return $this->busting_path . $this->get_busting_file_name( $locale );
	}

	/** ----------------------------------------------------------------------------------------- */
	/** TOOLS =================================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Get the contents of a URL.
	 *
	 * @since  3.2
	 * @access protected
	 * @author Grégory Viguier
	 *
	 * @param  string $url The URL to request.
	 * @return string|bool The contents on success. False on failure.
	 */
	protected function get_file_content( $url ) {
		try {
			$response = wp_remote_get( $url );
		} catch ( \Exception $e ) {
			Logger::error(
				'Remote file could not be fetched.',
				[
					'fb sdk',
					'url'      => $url,
					'response' => $e->getMessage(),
				]
			);
			return false;
		}

		if ( is_wp_error( $response ) ) {
			Logger::error(
				'Remote file could not be fetched.',
				[
					'fb sdk',
					'url'      => $url,
					'response' => $response->get_error_message(),
				]
			);
			return false;
		}

		$contents = wp_remote_retrieve_body( $response );

		if ( ! $contents ) {
			Logger::error(
				'Remote file could not be fetched.',
				[
					'fb sdk',
					'url'      => $url,
					'response' => $response,
				]
			);
			return false;
		}

		return $contents;
	}

	/**
	 * Escape a file name, to be used in a regex pattern (delimiter is `/`).
	 * `%s` conversion specifications are protected.
	 *
	 * @since  3.2
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @param  string $file_name The file name.
	 * @return string
	 */
	private function escape_file_name( $file_name ) {
		$file_name = explode( '%s', $file_name );
		$file_name = array_map( 'preg_quote', $file_name );

		return implode( '%s', $file_name );
	}
}
