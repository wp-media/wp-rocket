<?php
namespace WP_Rocket\Engine\Optimization\Minify\CSS;

use MatthiasMullie\Minify as Minifier;
use WP_Rocket\Engine\Optimization\CSSTrait;
use WP_Rocket\Engine\Optimization\Minify\ProcessorInterface;
use WP_Rocket\Logger\Logger;

/**
 * Minify CSS files
 *
 * @since 3.1
 */
class Minify extends AbstractCSSOptimization implements ProcessorInterface {
	use CSSTrait;

	/**
	 * Minifies CSS files
	 *
	 * @since 3.1
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function optimize( $html ) {
		Logger::info( 'CSS MINIFICATION PROCESS STARTED.', [ 'css minification process' ] );

		$styles = $this->get_styles( $html );
		if ( ! $styles ) {
			return $html;
		}

		foreach ( $styles as $style ) {
			if ( $this->bailout_style( $style ) ) {
				continue;
			}

			$minify_url = $this->replace_url( $style['url'] );

			if ( ! $minify_url ) {
				Logger::error(
					'Style minification failed.',
					[
						'css minification process',
						'tag' => $style[0],
					]
				);
				continue;
			}

			$html = $this->replace_style( $style, $minify_url, $html );
		}

		return $html;
	}

	/**
	 * Replace old style tag with the minified tag.
	 *
	 * @param array  $style      Style matched data.
	 * @param string $minify_url Minified URL.
	 * @param string $html       HTML content.
	 *
	 * @return string
	 */
	protected function replace_style( $style, $minify_url, $html ) {
		$replace_style = str_replace( $style['url'], $minify_url, $style[0] );
		$replace_style = str_replace( '<link', '<link data-minify="1"', $replace_style );
		$html          = str_replace( $style[0], $replace_style, $html );

		Logger::info(
			'Style minification succeeded.',
			[
				'css minification process',
				'url' => $minify_url,
			]
		);

		return $html;
	}

	/**
	 * Check if style should be bailout (is external or is excluded).
	 *
	 * @param  array $style Style matched data.
	 * @return bool
	 */
	protected function bailout_style( $style ) {
		if ( $this->is_external_file( $style['url'] ) ) {
			Logger::debug(
				'Style is external.',
				[
					'css minification process',
					'tag' => $style[0],
				]
			);
			return true;
		}

		if ( $this->is_minify_excluded_file( $style ) ) {
			Logger::debug(
				'Style is excluded.',
				[
					'css minification process',
					'tag' => $style[0],
				]
			);
			return true;
		}
		return false;
	}

	/**
	 * Get all style tags from HTML.
	 *
	 * @param  string $html       HTML content.
	 * @return array|bool $styles Array with style tags or false.
	 */
	protected function get_styles( $html ) {
		$html_nocomments = $this->hide_comments( $html );
		$styles          = $this->find( '<link\s+([^>]+[\s"\'])?href\s*=\s*[\'"]\s*?(?<url>[^\'"]+\.css(?:\?[^\'"]*)?)\s*?[\'"]([^>]+)?\/?>', $html_nocomments );

		if ( ! $styles ) {
			Logger::debug( 'No `<link>` tags found.', [ 'css minification process' ] );
			return false;
		}

		Logger::debug(
			'Found ' . count( $styles ) . ' `<link>` tags.',
			[
				'css minification process',
				'tags' => $styles,
			]
		);

		return $styles;
	}

	/**
	 * Creates the minify URL if the minification is successful
	 *
	 * @since 2.11
	 *
	 * @param string $url Original file URL.

	 * @return string|bool The minify URL if successful, false otherwise
	 */
	private function replace_url( $url ) {
		if ( empty( $url ) ) {
			return false;
		}

		// This filter is documented in /inc/classes/optimization/class-abstract-optimization.php.
		$url           = apply_filters( 'rocket_asset_url', $url, $this->get_zones() );
		$unique_id     = md5( $url . $this->minify_key );
		$filename      = preg_replace( '/\.(css)$/', '-' . $unique_id . '.css', ltrim( rocket_realpath( wp_parse_url( $url, PHP_URL_PATH ) ), '/' ) );
		$minified_file = $this->minify_base_path . $filename;
		$minify_url    = $this->get_minify_url( $filename, $url );

		if ( rocket_direct_filesystem()->exists( $minified_file ) ) {
			Logger::debug(
				'Minified CSS file already exists.',
				[
					'css minification process',
					'path' => $minified_file,
				]
			);
			return $minify_url;
		}

		$file_path = $this->get_file_path( $url );
		if ( ! $file_path ) {
			Logger::error(
				'Couldn’t get the file path from the URL.',
				[
					'css minification process',
					'url' => $url,
				]
			);
			return false;
		}

		$minified_content = $this->minify( $file_path, $minified_file );
		if ( ! $minified_content ) {
			return false;
		}

		$minified_content = $this->font_display_swap( $url, $minified_file, $minified_content );
		if ( ! $minified_content ) {
			return false;
		}

		$save_minify_file = $this->save_minify_css_file( $minified_file, $minified_content );
		if ( ! $save_minify_file ) {
			return false;
		}

		return $minify_url;
	}

	/**
	 * Save minified CSS file.
	 *
	 * @since 3.7
	 *
	 * @param string $minified_file    Minified file path.
	 * @param string $minified_content Minified HTML content.
	 *
	 * @return bool
	 */
	protected function save_minify_css_file( $minified_file, $minified_content ) {
		$save_minify_file = $this->write_file( $minified_content, $minified_file );
		if ( ! $save_minify_file ) {
			Logger::error(
				'Minified CSS file could not be created.',
				[
					'css minification process',
					'path' => $minified_file,
				]
			);
			return false;
		}
		Logger::debug(
			'Minified CSS file successfully created.',
			[
				'css minification process',
				'path' => $minified_file,
			]
		);
		return true;
	}

	/**
	 * Applies font display swap if the file contains @font-face.
	 *
	 * @since 3.7
	 *
	 * @param string $url           File Url.
	 * @param string $minified_file Minified file path.
	 * @param string $content       CSS file content.
	 */
	protected function font_display_swap( $url, $minified_file, $content ) {
		if ( preg_match( '/(?:-|\.)min.css/iU', $url )
			&&
			false === stripos( $content, '@font-face' ) ) {
			Logger::error(
				'Do not apply font display swap on min.css files without font-face.',
				[
					'css minification process',
					'path' => $minified_file,
				]
			);
			return false;
		}

		return $this->apply_font_display_swap( $content );
	}
	/**
	 * Minifies the content
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @param string|array $file          File to minify.
	 * @param string       $minified_file Target filepath.
	 * @return string|bool
	 */
	protected function minify( $file, $minified_file ) {
		$file_content = $this->get_file_content( $file );

		if ( ! $file_content ) {
			Logger::error(
				'No file content.',
				[
					'css minification process',
					'path' => $minified_file,
				]
			);
			return false;
		}

		$file_content     = $this->rewrite_paths( $file, $minified_file, $file_content );
		$minifier         = $this->get_minifier( $file_content );
		$minified_content = $minifier->minify();

		if ( empty( $minified_content ) ) {
			Logger::error(
				'No minified content.',
				[
					'css minification process',
					'path' => $minified_file,
				]
			);
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
