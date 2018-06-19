<?php
namespace WP_Rocket\Optimization\JS;

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
	 * @return string
	 */
	public function optimize() {
		$nodes = $this->find( 'script[src*=".js"]' );

		if ( ! $nodes ) {
			return $this->crawler->saveHTML();
		}

		$nodes->each( function( \Wa72\HtmlPageDom\HtmlPageCrawler $node, $i ) {
			global $wp_scripts;

			$src = $node->attr( 'src' );

			if ( preg_match( '/[-.]min\.js/iU', $src ) ) {
				return;
			}

			if ( $this->is_external_file( $src ) ) {
				return;
			}

			if ( $this->is_minify_excluded_file( $node ) ) {
				return;
			}

			// Don't minify jQuery included in WP core since it's already minified but without .min in the filename.
			if ( ! empty( $wp_scripts->registered['jquery-core']->src ) && false !== strpos( $src, $wp_scripts->registered['jquery-core']->src ) ) {
				return;
			}

			$minify_url = $this->replace_url( $src );

			if ( ! $minify_url ) {
				return;
			}

			$node->attr( 'src', $minify_url );
			$node->attr( 'data-minify', '1' );

			return;
		} );

		return $this->crawler->saveHTML();
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

		if ( ! rocket_direct_filesystem()->exists( $minified_file ) ) {
			$minified_content = $this->minify( $this->get_file_path( $url ) );

			if ( ! $minified_content ) {
				return false;
			}

			$save_minify_file = $this->write_file( $minified_content, $minified_file );

			if ( ! $save_minify_file ) {
				return false;
			}
		}

		return $this->get_minify_url( $filename );
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
