<?php
namespace WP_Rocket\Busting;

use WP_Rocket\Busting\Google_Analytics;

/**
 * Manages the cache busting of the Google Tag Manager file
 *
 * @since 3.1
 * @author Remy Perona
 */
class Google_Tag_Manager extends Abstract_Busting {
	/**
	 * {@inheritdoc}
	 */
	public function __construct( $busting_path, $busting_url, Google_Analytics $ga_busting ) {
		$blog_id            = get_current_blog_id();
		$this->busting_path = $busting_path . $blog_id . '/';
		$this->busting_url  = $busting_url . $blog_id . '/';
		$this->filename     = 'gtm-local.js';
		$this->ga_busting   = $ga_busting;
	}

	/**
	 * {@inheritdoc}
	 */
	public function replace_url( $html ) {
		$script = $this->find( '<script(\s+[^>]+)?\s+src\s*=\s*[\'"]\s*?((?:https?:)?\/\/www\.googletagmanager\.com(?:.+)?)\s*?[\'"]([^>]+)?\/?>', $html );

		if ( ! $script ) {
			return $html;
		}

		if ( ! $this->save( $script[2] ) ) {
			return $html;
		}

		$replace_script = str_replace( $script[2], $this->get_busting_url(), $script[0] );
		$replace_script = str_replace( '<script', '<script data-no-minify="1"', $replace_script );
		$html           = str_replace( $script[0], $replace_script, $html );

		return $html;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function find( $pattern, $html ) {
		preg_match_all( '/' . $pattern . '/Umsi', $html, $matches, PREG_SET_ORDER );

		if ( empty( $matches ) ) {
			return false;
		}

		return $matches[0];
	}

	/**
	 * Saves the content of the URL to bust to the busting file if it doesn't exist yet.
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $url      URL to get the content from.
	 * @return bool
	 */
	public function save( $url ) {
		$path = $this->busting_path . $this->filename;

		if ( \rocket_direct_filesystem()->exists( $path ) ) {
			return true;
		}

		$content = $this->get_file_content( $url );

		if ( ! $content ) {
			return false;
		}

		$content = $this->replace_ga_url( $content );

		if ( ! \rocket_direct_filesystem()->exists( $this->busting_path ) ) {
			\rocket_mkdir_p( $this->busting_path );
		}

		if ( ! \rocket_put_content( $path, $content ) ) {
			return false;
		}

		return true;
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
