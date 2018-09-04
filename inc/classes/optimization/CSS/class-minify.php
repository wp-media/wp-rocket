<?php
namespace WP_Rocket\Optimization\CSS;

use WP_Rocket\Logger;
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
		Logger::info( 'CSS MINIFICATION PROCESS STARTED.', [ 'css minification process' ] );

		$html_nocomments = $this->hide_comments( $html );
		$styles          = $this->find( '<link\s+([^>]+[\s"\'])?href\s*=\s*[\'"]\s*?([^\'"]+\.css(?:\?[^\'"]*)?)\s*?[\'"]([^>]+)?\/?>', $html_nocomments );

		if ( ! $styles ) {
			Logger::debug( 'No `<link>` tags found.', [ 'css minification process' ] );
			return $html;
		}

		Logger::debug( 'Found ' . count( $styles ) . ' `<link>` tags.', [
			'css minification process',
			'tags' => $styles,
		] );

		foreach ( $styles as $style ) {
			if ( preg_match( '/(?:-|\.)min.css/iU', $style[2] ) ) {
				Logger::debug( 'Style is already minified.', [
					'css minification process',
					'tag' => $style[0],
				] );
				continue;
			}

			if ( $this->is_external_file( $style[2] ) ) {
				Logger::debug( 'Style is external.', [
					'css minification process',
					'tag' => $style[0],
				] );
				continue;
			}

			if ( $this->is_minify_excluded_file( $style ) ) {
				Logger::debug( 'Style is excluded.', [
					'css minification process',
					'tag' => $style[0],
				] );
				continue;
			}

			$minify_url = $this->replace_url( $style[2] );

			if ( ! $minify_url ) {
				Logger::error( 'Style minification failed.', [
					'css minification process',
					'tag' => $style[0],
				] );
				continue;
			}

			$replace_style = str_replace( $style[2], $minify_url, $style[0] );
			$replace_style = str_replace( '<link', '<link data-minify="1"', $replace_style );
			$html          = str_replace( $style[0], $replace_style, $html );

			Logger::info( 'Style minification succeeded.', [
				'css minification process',
				'url' => $minify_url,
			] );
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
			Logger::debug( 'Minified CSS file already exists.', [
				'css minification process',
				'path' => $minified_file,
			] );
			return $minify_url;
		}

		$file_path = $this->get_file_path( $url );

		if ( ! $file_path ) {
			Logger::error( 'Couldn’t get the file path from the URL.', [
				'css minification process',
				'url' => $url,
			] );
			return false;
		}

		$minified_content = $this->minify( $file_path );

		if ( ! $minified_content ) {
			Logger::error( 'No minified content.', [
				'css minification process',
				'path' => $minified_file,
			] );
			return false;
		}

		$save_minify_file = $this->write_file( $minified_content, $minified_file );

		if ( ! $save_minify_file ) {
			Logger::error( 'Minified CSS file could not be created.', [
				'css minification process',
				'path' => $minified_file,
			] );
			return false;
		}

		Logger::debug( 'Minified CSS file successfully created.', [
			'css minification process',
			'path' => $minified_file,
		] );

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
