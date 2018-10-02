<?php
namespace WP_Rocket\Busting;

/**
 * Manages the cache busting of the Facebook Pixel files.
 *
 * @since  3.2
 * @author Grégory Viguier
 */
class Facebook_Pickles extends Abstract_Busting {
	/**
	 * Facebook Pixel URL.
	 *
	 * @var    string
	 * @since  3.2
	 * @access protected
	 * @author Grégory Viguier
	 */
	protected $url;

	/**
	 * Flag to track the replacement.
	 *
	 * @var    bool
	 * @since  3.2
	 * @access protected
	 * @author Grégory Viguier
	 */
	protected $is_replaced;

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
		$this->busting_path = $busting_path . 'facebook-tracking/';
		$this->busting_url  = $busting_url . 'facebook-tracking/';
		$this->is_replaced  = false;
		/** Warning: the file name and script URL must be run through sprintf() with a locale string like 'en_US'. */
		$this->filename     = 'fbpix-local-%s.js';
		$this->url          = 'https://connect.facebook.net/%s/fbevents.js';
	}

	/**
	 * Perform the URL replacement process.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  string $html HTML content.
	 * @return string
	 */
	public function replace_url( $html ) {
		$tag = $this->find( '<script[^>]*?>(.*)<\/script>', $html );

		if ( ! $tag ) {
			return $html;
		}

		$locale = $this->get_locale_from_url( $tag );

		if ( ! $this->save( $this->get_url( $locale ) ) ) {
			return $html;
		}

		$replace_tag = preg_replace( '@(?:https?:)?//connect\.facebook\.net/[^/]+/fbevents\.js@i', $this->get_busting_url( $locale ), $tag );
		$html        = str_replace( $tag, $replace_tag, $html );

		$this->is_replaced = true;

		return $html;
	}

	/**
	 * Search for element in the DOM.
	 *
	 * @since  3.2
	 * @access protected
	 * @author Grégory Viguier
	 *
	 * @param  string $pattern Pattern to match.
	 * @param  string $html    HTML content.
	 * @return string
	 */
	protected function find( $pattern, $html ) {
		preg_match_all( '/' . $pattern . '/Umsi', $html, $matches, PREG_SET_ORDER );

		if ( empty( $matches ) ) {
			return false;
		}

		foreach ( $matches as list( $tag, $script ) ) {
			if ( $script && preg_match( '@fbq\s*\(\s*["\']init["\']\s*,@', $script ) ) {
				return $tag;
			}
		}

		return false;
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

	/**
	 * Save the content of the URL to bust into the busting file if it doesn't exist yet.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  string $url URL to get the content from.
	 * @return bool        True on success. False on failure.
	 */
	public function save( $url ) {
		$locale = $this->get_locale_from_url( $url );
		$path   = $this->get_busting_file_path( $locale );

		if ( \rocket_direct_filesystem()->exists( $path ) ) {
			// If a previous version is present, it is kept in place.
			$events_content = null;
			return true;
		}

		return $this->refresh_save( $url );
	}

	/**
	 * Save the content of the URL to bust into the busting file.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  string $url URL to get the content from.
	 * @return bool        True on success. False on failure.
	 */
	public function refresh_save( $url ) {
		$content = $this->get_file_content( $url );

		if ( ! $content ) {
			// Error, we couldn't fetch the file content.
			return false;
		}

		if ( ! \rocket_direct_filesystem()->exists( $this->busting_path ) ) {
			\rocket_mkdir_p( $this->busting_path );
		}

		$locale = $this->get_locale_from_url( $url );
		$path   = $this->get_busting_file_path( $locale );

		if ( ! \rocket_put_content( $path, $content ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Look for existing busting files and update their content.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 */
	public function refresh_save_all() {
		$locales = $this->get_locales_in_use();

		if ( ! $locales ) {
			return;
		}

		foreach ( $locales as $locale ) {
			$this->refresh_save( $this->get_url( $locale ) );
		}
	}

	/**
	 * Delete all Facebook Pixel busting files.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function delete() {
		$locales = $this->get_locales_in_use();

		if ( ! $locales ) {
			return;
		}

		$filesystem = \rocket_direct_filesystem();

		foreach ( $locales as $locale ) {
			$filesystem->delete( $this->get_busting_file_path( $locale ), false, 'f' );
		}
	}

	/**
	 * Get the final URL for a cache busting file.
	 *
	 * @since  3.2
	 * @access protected
	 * @author Grégory Viguier
	 *
	 * @return string
	 */
	protected function get_busting_url( /** $locale = '' */ ) {
		$args     = func_get_args();
		$locale   = ! empty( $args[0] ) ? $args[0] : 'en_US';
		$filename = $this->get_busting_file_name( $locale );

		// This filter is documented in inc/functions/minify.php.
		return apply_filters( 'rocket_js_url', get_rocket_cdn_url( $this->busting_url . $filename, array( 'all', 'css_and_js', 'js' ) ) );
	}

	/**
	 * Get a Facebook Pixel URL.
	 *
	 * @since  3.2
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return string
	 */
	public function get_url( /** $locale = '' */ ) {
		$args   = func_get_args();
		$locale = ! empty( $args[0] ) ? $args[0] : 'en_US';

		return sprintf( $this->url, $locale );
	}

	/**
	 * Get the locales of the files currently in the cache busting folder.
	 *
	 * @since  3.2
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @return array
	 */
	private function get_locales_in_use() {
		$filesystem = \rocket_direct_filesystem();

		if ( ! $filesystem->is_writable( $this->busting_path ) ) {
			return [];
		}

		$dir = $filesystem->dirlist( $this->busting_path );

		if ( ! $dir ) {
			return [];
		}

		$locales = [];

		foreach ( $dir as $entry ) {
			if ( preg_match( '@^fbpix-local-(?<locale>.+)\.js$@', $entry['name'], $matches ) ) {
				$locales[] = $matches['locale'];
			}
		}

		return $locales;
	}

	/**
	 * Extract the locale from a URL to bust.
	 *
	 * @since  3.2
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @param  string $url Any string containing the URL to bust.
	 * @return string
	 */
	private function get_locale_from_url( $url ) {
		preg_match( '@//connect\.facebook\.net/(?<locale>[^/]+)/fbevents\.js@i', $url, $matches );

		return ! empty( $matches['locale'] ) ? $matches['locale'] : 'en_US';
	}

	/**
	 * Get a busting file name.
	 *
	 * @since  3.2
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @param  string $locale A locale string, like 'en_US'.
	 * @return string
	 */
	private function get_busting_file_name( $locale ) {
		$locale = $locale ? $locale : 'en_US';

		return sprintf( $this->filename, $locale );
	}

	/**
	 * Get a busting file path.
	 *
	 * @since  3.2
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @param  string $locale A locale string, like 'en_US'.
	 * @return string
	 */
	private function get_busting_file_path( $locale ) {
		return $this->busting_path . $this->get_busting_file_name( $locale );
	}
}
