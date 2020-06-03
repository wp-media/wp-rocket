<?php
namespace WP_Rocket\Engine\Optimization\Minify\CSS;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Logger\Logger;
use WP_Rocket\Optimization\CSS\Path_Rewriter;
use MatthiasMullie\Minify\CSS as MinifyCSS;

/**
 * Minify & Combine CSS files
 *
 * @since 3.1
 * @author Remy Perona
 */
class Combine extends AbstractCSSOptimization {
	use Path_Rewriter;

	/**
	 * Minifier instance
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @var MinifyCSS
	 */
	private $minifier;

	/**
	 * Constructor
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param Options_Data $options  Options instance.
	 * @param MinifyCSS    $minifier Minifier instance.
	 */
	public function __construct( Options_Data $options, MinifyCSS $minifier ) {
		parent::__construct( $options );

		$this->minifier = $minifier;
	}

	/**
	 * Minifies and combines all CSS files into one
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function optimize( $html ) {
		Logger::info( 'CSS COMBINE PROCESS STARTED.', [ 'css combine process' ] );

		$html_nocomments = $this->hide_comments( $html );
		$styles          = $this->find( '<link\s+([^>]+[\s"\'])?href\s*=\s*[\'"]\s*?(?<url>[^\'"]+\.css(?:\?[^\'"]*)?)\s*?[\'"]([^>]+)?\/?>', $html_nocomments );

		if ( ! $styles ) {
			Logger::debug( 'No `<link>` tags found.', [ 'css combine process' ] );
			return $html;
		}

		Logger::debug(
			'Found ' . count( $styles ) . ' `<link>` tag(s).',
			[
				'css combine process',
				'tags' => $styles,
			]
		);

		$files  = [];
		$styles = array_map(
				function( $style ) use ( &$files ) {
					if ( $this->is_external_file( $style['url'] ) ) {
						Logger::debug(
							'Style is external.',
							[
								'css combine process',
								'tag' => $style[0],
							]
						);
						return;
					}

					if ( $this->is_minify_excluded_file( $style ) ) {
						Logger::debug(
							'Style is excluded.',
							[
								'css combine process',
								'tag' => $style[0],
							]
						);
						return;
					}

					$style_filepath = $this->get_file_path( $style['url'] );

					if ( ! $style_filepath ) {
						return;
					}

					$files[] = $style_filepath;

					return $style;
				},
			$styles
			);

		$styles = array_filter( $styles );

		if ( empty( $styles ) ) {
			Logger::debug( 'No `<link>` tags to optimize.', [ 'css combine process' ] );
			return $html;
		}

		Logger::debug(
			count( $styles ) . ' `<link>` tag(s) remaining.',
			[
				'css combine process',
				'tags' => $styles,
			]
		);

		$minify_url = $this->combine( $files );

		if ( ! $minify_url ) {
			Logger::error( 'CSS combine process failed.', [ 'css combine process' ] );
			return $html;
		}

		$html = $this->insert_combined_css( $html, $minify_url, $styles );

		Logger::info(
			'Combined CSS file successfully added.',
			[
				'css combine process',
				'url' => $minify_url,
			]
		);

		return $html;
	}

	/**
	 * Insert the combined CSS file and remove the original CSS tags
	 *
	 * The combined CSS file is added after the closing </title> tag, and the replacement occurs only once. The original CSS tags are then removed from the HTML.
	 *
	 * @since 3.3.3
	 * @author Remy Perona
	 *
	 * @param string $html      HTML content.
	 * @param string $css_url   Combined CSS file URL.
	 * @param array  $to_remove An array of CSS tags to remove.
	 * @return string
	 */
	protected function insert_combined_css( $html, $css_url, array $to_remove ) {
		// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
		$html = preg_replace( '/<\/title>/i', '$0<link rel="stylesheet" href="' . $css_url . '" data-minify="1" />', $html, 1 );

		foreach ( $to_remove as $style ) {
			$html = str_replace( $style[0], '', $html );
		}

		return $html;
	}

	/**
	 * Creates the minify URL if the minification is successful
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @param array $files Files to minify.

	 * @return string|bool The minify URL if successful, false otherwise
	 */
	protected function combine( $files ) {
		if ( empty( $files ) ) {
			return false;
		}

		$file_hash = implode( ',', $files );
		$filename  = md5( $file_hash . $this->minify_key ) . '.css';

		$minified_file = $this->minify_base_path . $filename;

		if ( ! rocket_direct_filesystem()->exists( $minified_file ) ) {
			$minified_content = $this->minify( $files, $minified_file );

			if ( ! $minified_content ) {
				Logger::error(
					'No minified content.',
					[
						'css combine process',
						'path' => $minified_file,
					]
				);
				return false;
			}

			$minify_filepath = $this->write_file( $minified_content, $minified_file );

			if ( ! $minify_filepath ) {
				Logger::error(
					'Minified CSS file could not be created.',
					[
						'css combine process',
						'path' => $minified_file,
					]
				);
				return false;
			}

			Logger::debug(
				'Combined CSS file successfully created.',
				[
					'css combine process',
					'path' => $minified_file,
				]
			);
		} else {
			Logger::debug(
				'Combined CSS file already exists.',
				[
					'css combine process',
					'path' => $minified_file,
				]
			);
		}

		return $this->get_minify_url( $filename );
	}

	/**
	 * Minifies the content
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @param string|array $files         Files to minify.
	 * @param string       $minified_file Target filepath.
	 * @return string|bool
	 */
	protected function minify( $files, $minified_file ) {
		foreach ( $files as $file ) {
			$file_content = $this->get_file_content( $file );
			$file_content = $this->rewrite_paths( $file, $minified_file, $file_content );

			$this->minifier->add( $file_content );
		}

		$minified_content = $this->minifier->minify();

		if ( empty( $minified_content ) ) {
			return false;
		}

		return $minified_content;
	}
}
