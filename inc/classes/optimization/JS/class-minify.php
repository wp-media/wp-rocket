<?php
namespace WP_Rocket\Optimization\JS;

use WP_Rocket\Logger;
use MatthiasMullie\Minify as Minifier;

/**
 * Minify JS files
 *
 * @since 3.1
 * @author Remy Perona
 */
class Minify extends Abstract_JS_Optimization {
	/**
	 * Minifies JS files
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function optimize( $html ) {
		Logger::info( 'JS MINIFICATION PROCESS STARTED.', [ 'js minification process' ] );

		$html_nocomments = $this->hide_comments( $html );
		$scripts         = $this->find( '<script\s+([^>]+[\s\'"])?src\s*=\s*[\'"]\s*?([^\'"]+\.js(?:\?[^\'"]*)?)\s*?[\'"]([^>]+)?\/?>', $html_nocomments );

		if ( ! $scripts ) {
			Logger::debug( 'No `<script>` tags found.', [ 'js minification process' ] );
			return $html;
		}

		Logger::debug( 'Found ' . count( $scripts ) . ' <link> tags.', [
			'js minification process',
			'tags' => $scripts,
		] );

		foreach ( $scripts as $script ) {
			global $wp_scripts;

			if ( preg_match( '/[-.]min\.js/iU', $script[2] ) ) {
				Logger::debug( 'Script is already minified.', [
					'js minification process',
					'tag' => $script[0],
				] );
				continue;
			}

			if ( $this->is_external_file( $script[2] ) ) {
				Logger::debug( 'Script is external.', [
					'js minification process',
					'tag' => $script[0],
				] );
				continue;
			}

			if ( $this->is_minify_excluded_file( $script ) ) {
				Logger::debug( 'Script is excluded.', [
					'js minification process',
					'tag' => $script[0],
				] );
				continue;
			}

			// Don't minify jQuery included in WP core since it's already minified but without .min in the filename.
			if ( ! empty( $wp_scripts->registered['jquery-core']->src ) && false !== strpos( $script[2], $wp_scripts->registered['jquery-core']->src ) ) {
				Logger::debug( 'jQuery script is already minified.', [
					'js minification process',
					'tag' => $script[0],
				] );
				continue;
			}

			$minify_url = $this->replace_url( $script[2] );

			if ( ! $minify_url ) {
				Logger::error( 'Script minification failed.', [
					'js minification process',
					'tag' => $script[0],
				] );
				continue;
			}

			$replace_script = str_replace( $script[2], $minify_url, $script[0] );
			$replace_script = str_replace( '<script', '<script data-minify="1"', $replace_script );
			$html           = str_replace( $script[0], $replace_script, $html );

			Logger::info( 'Script minification succeeded.', [
				'js minification process',
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
	protected function replace_url( $url ) {
		if ( empty( $url ) ) {
			return false;
		}

		$unique_id = md5( $url . $this->minify_key );
		$filename  = preg_replace( '/\.js$/', '-' . $unique_id . '.js', ltrim( rocket_realpath( rocket_extract_url_component( $url, PHP_URL_PATH ) ), '/' ) );

		$minified_file = $this->minify_base_path . $filename;
		$minified_url  = $this->get_minify_url( $filename );

		if ( rocket_direct_filesystem()->exists( $minified_file ) ) {
			Logger::debug( 'Minified JS file already exists.', [
				'js minification process',
				'path' => $minified_file,
			] );
			return $minified_url;
		}

		$file_path = $this->get_file_path( $url );

		if ( ! $file_path ) {
			Logger::error( 'Couldn’t get the file path from the URL.', [
				'js minification process',
				'url' => $url,
			] );
			return false;
		}

		$minified_content = $this->minify( $file_path );

		if ( ! $minified_content ) {
			Logger::error( 'No minified content.', [
				'js minification process',
				'path' => $minified_file,
			] );
			return false;
		}

		$save_minify_file = $this->write_file( $minified_content, $minified_file );

		if ( ! $save_minify_file ) {
			Logger::error( 'Minified JS file could not be created.', [
				'js minification process',
				'path' => $minified_file,
			] );
			return false;
		}

		Logger::debug( 'Minified JS file successfully created.', [
			'js minification process',
			'path' => $minified_file,
		] );

		return $minified_url;
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
	 * @return Minifier\JS
	 */
	protected function get_minifier( $file_content ) {
		return new Minifier\JS( $file_content );
	}
}
