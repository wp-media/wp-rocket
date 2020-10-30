<?php
namespace WP_Rocket\Engine\Optimization\Minify\JS;

use WP_Rocket\Dependencies\Minify as Minifier;
use WP_Rocket\Engine\Optimization\Minify\ProcessorInterface;
use WP_Rocket\Logger\Logger;

/**
 * Minify JS files
 *
 * @since 3.1
 */
class Minify extends AbstractJSOptimization implements ProcessorInterface {
	/**
	 * Minifies JS files
	 *
	 * @since 3.1
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function optimize( $html ) {
		Logger::info( 'JS MINIFICATION PROCESS STARTED.', [ 'js minification process' ] );

		$scripts = $this->get_scripts( $html );

		if ( empty( $scripts ) ) {
			return $html;
		}

		foreach ( $scripts as $script ) {
			global $wp_scripts;

			$is_external_url = $this->is_external_file( $script['url'] );

			if (
				! $is_external_url
				&&
				preg_match( '/[-.]min\.js/iU', $script['url'] )
			) {
				Logger::debug(
					'Script is already minified.',
					[
						'js minification process',
						'tag' => $script[0],
					]
				);
				continue;
			}

			if (
				$is_external_url
				&&
				$this->is_excluded_external( $script['url'] )
			) {
				continue;
			}

			if ( $this->is_minify_excluded_file( $script ) ) {
				Logger::debug(
					'Script is excluded.',
					[
						'js minification process',
						'tag' => $script[0],
					]
				);
				continue;
			}

			// Don't minify jQuery included in WP core since it's already minified but without .min in the filename.
			if ( ! empty( $wp_scripts->registered['jquery-core']->src ) && false !== strpos( $script['url'], $wp_scripts->registered['jquery-core']->src ) ) {
				Logger::debug(
					'jQuery script is already minified.',
					[
						'js minification process',
						'tag' => $script[0],
					]
				);
				continue;
			}

			$integrity_validated = $this->local_cache->validate_integrity( $script );

			if ( false === $integrity_validated ) {
				Logger::debug(
					'Script integrity attribute not valid.',
					[
						'js minification process',
						'tag' => $script[0],
					]
				);

				continue;
			}

			$script['final'] = $integrity_validated;

			$minify_url = $this->replace_url( strtok( $script['url'], '?' ) );

			if ( ! $minify_url ) {
				Logger::error(
					'Script minification failed.',
					[
						'js minification process',
						'tag' => $script[0],
					]
				);
				continue;
			}

			$html = $this->replace_script( $script, $minify_url, $html );
		}

		return $html;
	}

	/**
	 * Get all script tags from HTML.
	 *
	 * @param  string $html HTML content.
	 * @return array Array with script tags, empty array if no script tags found.
	 */
	private function get_scripts( $html ) {
		$html_nocomments = $this->hide_comments( $html );
		$scripts         = $this->find( '<script\s+([^>]+[\s\'"])?src\s*=\s*[\'"]\s*?(?<url>[^\'"]+\.js(?:\?[^\'"]*)?)\s*?[\'"]([^>]+)?\/?>', $html_nocomments );

		if ( ! $scripts ) {
			Logger::debug( 'No `<script>` tags found.', [ 'js minification process' ] );
			return [];
		}

		Logger::debug(
			'Found ' . count( $scripts ) . ' <link> tags.',
			[
				'js minification process',
				'tags' => $scripts,
			]
		);

		return $scripts;
	}

	/**
	 * Checks if the provided external URL is excluded from minify
	 *
	 * @since 3.7
	 *
	 * @param string $url External URL to check.
	 * @return boolean
	 */
	private function is_excluded_external( $url ) {
		$excluded_externals   = $this->get_excluded_external_file_path();
		$excluded_externals[] = 'google-analytics.com/analytics.js';

		foreach ( $excluded_externals as $excluded ) {
			if ( false !== strpos( $url, $excluded ) ) {
				Logger::debug(
					'Script is external.',
					[
						'js combine process',
						'url' => $url,
					]
				);
				return true;
			}
		}

		return false;
	}

	/**
	 * Creates the minify URL if the minification is successful
	 *
	 * @since 2.11
	 *
	 * @param string $url Original file URL.

	 * @return string|bool The minify URL if successful, false otherwise
	 */
	protected function replace_url( $url ) {
		if ( empty( $url ) ) {
			return false;
		}

		// This filter is documented in /inc/classes/optimization/class-abstract-optimization.php.
		$url       = apply_filters( 'rocket_asset_url', $url, $this->get_zones() );
		$unique_id = md5( $url . $this->minify_key );
		$filename  = preg_replace( '/\.js$/', '-' . $unique_id . '.js', ltrim( rocket_realpath( wp_parse_url( $url, PHP_URL_PATH ) ), '/' ) );

		$minified_file = rawurldecode( $this->minify_base_path . $filename );
		$minified_url  = $this->get_minify_url( $filename, $url );

		if ( rocket_direct_filesystem()->exists( $minified_file ) ) {
			Logger::debug(
				'Minified JS file already exists.',
				[
					'js minification process',
					'path' => $minified_file,
				]
			);
			return $minified_url;
		}

		$is_external_url = $this->is_external_file( $url );
		$file_path       = $is_external_url ? $this->local_cache->get_filepath( $url ) : $this->get_file_path( $url );

		if ( ! $file_path ) {
			Logger::error(
				'Couldn’t get the file path from the URL.',
				[
					'js minification process',
					'url' => $url,
				]
			);
			return false;
		}

		$file_content = $is_external_url ? $this->local_cache->get_content( rocket_add_url_protocol( $url ) ) : $this->get_file_content( $file_path );

		if ( empty( $file_content ) ) {
			Logger::error(
				'No file content.',
				[
					'js minification process',
					'path' => $file_path,
				]
			);
			return false;
		}

		$minified_content = $this->minify( $file_content );

		if ( empty( $minified_content ) ) {
			Logger::error(
				'No minified content.',
				[
					'js minification process',
					'path' => $minified_file,
				]
			);

			return false;
		}

		$save_minify_file = $this->save_minify_file( $minified_file, $minified_content );

		if ( ! $save_minify_file ) {
			return false;
		}

		return $minified_url;
	}

	/**
	 * Replace old script tag with the minified tag.
	 *
	 * @param array  $script     Script matched data.
	 * @param string $minify_url Minified URL.
	 * @param string $html       HTML content.
	 *
	 * @return string
	 */
	private function replace_script( $script, $minify_url, $html ) {
		$replace_script = str_replace( $script['url'], $minify_url, $script['final'] );
		$replace_script = str_replace( '<script', '<script data-minify="1"', $replace_script );
		$html           = str_replace( $script[0], $replace_script, $html );

		Logger::info(
			'Script minification succeeded.',
			[
				'js minification process',
				'url' => $minify_url,
			]
		);

		return $html;
	}

	/**
	 * Save minified JS file.
	 *
	 * @since 3.7
	 *
	 * @param string $minified_file    Minified file path.
	 * @param string $minified_content Minified HTML content.
	 *
	 * @return bool
	 */
	protected function save_minify_file( $minified_file, $minified_content ) {
		$save_minify_file = $this->write_file( $minified_content, $minified_file );

		if ( ! $save_minify_file ) {
			Logger::error(
				'Minified JS file could not be created.',
				[
					'js minification process',
					'path' => $minified_file,
				]
			);
			return false;
		}

		Logger::debug(
			'Minified JS file successfully created.',
			[
				'js minification process',
				'path' => $minified_file,
			]
		);

		return true;
	}

	/**
	 * Minifies the content
	 *
	 * @since 2.11
	 *
	 * @param string $file_content Content to minify.
	 * @return string
	 */
	protected function minify( $file_content ) {
		$minifier         = $this->get_minifier( $file_content );
		$minified_content = $minifier->minify();

		if ( empty( $minified_content ) ) {
			return '';
		}

		return $minified_content;
	}

	/**
	 * Returns a new minifier instance
	 *
	 * @since 3.1
	 *
	 * @param string $file_content Content to minify.
	 * @return Minifier\JS
	 */
	protected function get_minifier( $file_content ) {
		return new Minifier\JS( $file_content );
	}
}
