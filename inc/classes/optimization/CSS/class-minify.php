<?php
namespace WP_Rocket\Optimization\CSS;

use MatthiasMullie\Minify as Minifier;

/**
 * Minify CSS files
 *
 * @since 3.1
 * @author Remy Perona
 */
class Minify extends Abstract_CSS_Optimization {
	use Path_Rewriter;

	/**
	 * Minifies CSS files
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function optimize( $html ) {
		$html_nocomments = preg_replace( '/<!--(.*)-->/Uis', '', $html );
		$styles          = $this->find( '<link\s+([^>]+[\s"\'])?href\s*=\s*[\'"]\s*?([^\'"]+\.css(?:\?[^\'"]*)?)\s*?[\'"]([^>]+)?\/?>', $html_nocomments );

		if ( ! $styles ) {
			return $html;
		}

		foreach ( $styles as $style ) {
			if ( preg_match( '/(?:-|\.)min.css/iU', $style[2] ) ) {
				continue;
			}

			if ( $this->is_external_file( $style[2] ) ) {
				continue;
			}

			if ( $this->is_minify_excluded_file( $style ) ) {
				continue;
			}

			$minify_url = $this->replace_url( $style[2] );

			if ( ! $minify_url ) {
				continue;
			}

			$replace_style = str_replace( $style[2], $minify_url, $style[0] );
			$replace_style = str_replace( '<link', '<link data-minify="1"', $replace_style );
			$html          = str_replace( $style[0], $replace_style, $html );
		}

		return $html;
	}

	/**
	 * Creates the minify URL if the minification is successful
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @param string $url Original file URL.

	 * @return string|bool The minify URL if successful, false otherwise
	 */
	private function replace_url( $url ) {
		if ( empty( $url ) ) {
			return false;
		}

		$unique_id = md5( $url . $this->minify_key );
		$filename  = preg_replace( '/\.(css)$/', '-' . $unique_id . '.css', ltrim( rocket_realpath( rocket_extract_url_component( $url, PHP_URL_PATH ) ), '/' ) );

		$minified_file = $this->minify_base_path . $filename;
		$minify_url    = $this->get_minify_url( $filename );

		if ( rocket_direct_filesystem()->exists( $minified_file ) ) {
			return $minify_url;
		}

		$file_path = $this->get_file_path( $url );

		if ( ! $file_path ) {
			return false;
		}

		$minified_content = $this->minify( $file_path );

		if ( ! $minified_content ) {
			return false;
		}

		$save_minify_file = $this->write_file( $minified_content, $minified_file );

		if ( ! $save_minify_file ) {
			return false;
		}

		return $minify_url;
	}

	/**
	 * Minifies the content
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @param string|array $file     File to minify.
	 * @return string|bool Minified content, false if empty
	 */
	protected function minify( $file ) {
		$file_content = $this->get_file_content( $file );

		if ( ! $file_content ) {
			return false;
		}

		$file_content     = $this->rewrite_paths( $file, $file_content );
		$minifier         = $this->get_minifier( $file_content );
		$minified_content = $minifier->minify();

		if ( empty( $minified_content ) ) {
			return false;
		}

		return $minified_content;
	}

	/**
	 * Returns a new minifier instance
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $file_content Content to minify.
	 * @return Minifier\CSS
	 */
	protected function get_minifier( $file_content ) {
		return new Minifier\CSS( $file_content );
	}
}
