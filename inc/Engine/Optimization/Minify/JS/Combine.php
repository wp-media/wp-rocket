<?php
namespace WP_Rocket\Engine\Optimization\Minify\JS;

use WP_Rocket\Dependencies\Minify\JS as MinifyJS;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\AssetsLocalCache;
use WP_Rocket\Engine\Optimization\DeferJS\DeferJS;
use WP_Rocket\Engine\Optimization\Minify\ProcessorInterface;
use WP_Rocket\Logger\Logger;

/**
 * Combines JS files
 *
 * @since 3.1
 */
class Combine extends AbstractJSOptimization implements ProcessorInterface {
	/**
	 * Minifier instance
	 *
	 * @since 3.1
	 *
	 * @var MinifyJS
	 */
	private $minifier;

	/**
	 * Excluded defer JS pattern
	 *
	 * @since 3.8
	 *
	 * @var string
	 */
	private $excluded_defer_js;

	/**
	 * Scripts to combine
	 *
	 * @since 3.1
	 *
	 * @var array
	 */
	private $scripts = [];

	/**
	 * Inline scripts excluded from combined and moved after the combined file
	 *
	 * @since 3.1.4
	 *
	 * @var array
	 */
	private $move_after = [];

	/**
	 * Constructor
	 *
	 * @since 3.1
	 *
	 * @param Options_Data     $options     Plugin options instance.
	 * @param MinifyJS         $minifier    Minifier instance.
	 * @param AssetsLocalCache $local_cache Assets local cache instance.
	 * @param DeferJS          $defer_js    Defer JS instance.
	 */
	public function __construct( Options_Data $options, MinifyJS $minifier, AssetsLocalCache $local_cache, DeferJS $defer_js ) {
		parent::__construct( $options, $local_cache );

		$this->minifier          = $minifier;
		$this->excluded_defer_js = implode( '|', $defer_js->get_excluded() );
	}

	/**
	 * Minifies and combines JavaScripts into one
	 *
	 * @since 3.1
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function optimize( $html ) {
		Logger::info( 'JS COMBINE PROCESS STARTED.', [ 'js combine process' ] );

		$html_nocomments = $this->hide_comments( $html );
		$scripts         = $this->find( '<script.*<\/script>', $html_nocomments );

		if ( ! $scripts ) {
			Logger::debug( 'No `<script>` tags found.', [ 'js combine process' ] );
			return $html;
		}

		Logger::debug(
			'Found ' . count( $scripts ) . ' `<script>` tag(s).',
			[
				'js combine process',
				'tags' => $scripts,
			]
		);

		$combine_scripts = $this->parse( $scripts );

		if ( empty( $combine_scripts ) ) {
			Logger::debug( 'No `<script>` tags to optimize.', [ 'js combine process' ] );
			return $html;
		}

		Logger::debug(
			count( $combine_scripts ) . ' `<script>` tag(s) remaining.',
			[
				'js combine process',
				'tags' => $combine_scripts,
			]
		);

		$content = $this->get_content();

		if ( empty( $content ) ) {
			Logger::debug( 'No JS content.', [ 'js combine process' ] );
			return $html;
		}

		$minify_url = $this->combine( $content );

		if ( ! $minify_url ) {
			Logger::error( 'JS combine process failed.', [ 'js combine process' ] );
			return $html;
		}

		$move_after = '';

		if ( ! empty( $this->move_after ) ) {
			foreach ( $this->move_after as $script ) {
				$move_after .= $script;
				$html        = str_replace( $script, '', $html );
			}
		}

		// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
		$html = str_replace( '</body>', '<script src="' . esc_url( $minify_url ) . '" data-minify="1"></script>' . $move_after . '</body>', $html );

		foreach ( $combine_scripts as $script ) {
			$html = str_replace( $script[0], '', $html );
		}

		Logger::info(
			'Combined JS file successfully added.',
			[
				'js combine process',
				'url' => $minify_url,
			]
		);

		return $html;
	}

	/**
	 * Parses found nodes to keep only the ones to combine
	 *
	 * @since 3.1
	 *
	 * @param Array $scripts scripts corresponding to JS file or content.
	 * @return array
	 */
	protected function parse( $scripts ) {
		$excluded_externals = implode( '|', $this->get_excluded_external_file_path() );
		$scripts            = array_map(
			function ( $script ) use ( $excluded_externals ) {
				preg_match( '/<script\s+([^>]+[\s\'"])?src\s*=\s*[\'"]\s*?(?<url>[^\'"]+\.js(?:\?[^\'"]*)?)\s*?[\'"]([^>]+)?\/?>/Umsi', $script[0], $matches );

				if ( isset( $matches['url'] ) ) {
					if ( $this->is_external_file( $matches['url'] ) ) {
						if ( preg_match( '#(' . $excluded_externals . ')#', $matches['url'] ) ) {
							Logger::debug(
								'Script is external.',
								[
									'js combine process',
									'tag' => $matches[0],
								]
							);
							return;
						}

						if ( $this->is_defer_excluded( $matches['url'] ) ) {
							return;
						}

						$this->scripts[] = [
							'type'    => 'url',
							'content' => $matches['url'],
						];

						return $script;
					}

					if ( $this->is_minify_excluded_file( $matches ) ) {
						Logger::debug(
							'Script is excluded.',
							[
								'js combine process',
								'tag' => $matches[0],
							]
						);
						return;
					}

					if ( $this->is_defer_excluded( $matches['url'] ) ) {
						return;
					}

					$file_path = $this->get_file_path( strtok( $matches['url'], '?' ) );

					if ( ! $file_path ) {
						return;
					}

					$this->scripts[] = [
						'type'    => 'file',
						'content' => $file_path,
					];
				} else {
					preg_match( '/<script\b(?<attrs>[^>]*)>(?:\/\*\s*<!\[CDATA\[\s*\*\/)?\s*(?<content>[\s\S]*?)\s*(?:\/\*\s*\]\]>\s*\*\/)?<\/script>/msi', $script[0], $matches_inline );

					$matches_inline = array_merge(
						[
							'attrs'   => '',
							'content' => '',
						],
						$matches_inline
					);

					if ( preg_last_error() === PREG_BACKTRACK_LIMIT_ERROR ) {
						Logger::debug(
							'PCRE regex execution Catastrophic Backtracking',
							[
								'inline JS backtracking error',
								'content' => $matches_inline['content'],
							]
						);
						return;
					}

					if ( strpos( $matches_inline['attrs'], 'type' ) !== false && ! preg_match( '/type\s*=\s*["\']?(?:text|application)\/(?:(?:x\-)?javascript|ecmascript)["\']?/i', $matches_inline['attrs'] ) ) {
						Logger::debug(
							'Inline script is not JS.',
							[
								'js combine process',
								'attributes' => $matches_inline['attrs'],
							]
						);
						return;
					}

					if ( false !== strpos( $matches_inline['attrs'], 'src=' ) ) {
						Logger::debug(
							'Inline script has a `src` attribute.',
							[
								'js combine process',
								'attributes' => $matches_inline['attrs'],
							]
						);
						return;
					}

					if ( in_array( $matches_inline['content'], $this->get_localized_scripts(), true ) ) {
						Logger::debug(
							'Inline script is a localize script',
							[
								'js combine process',
								'excluded_content' => $matches_inline['content'],
							]
						);
						return;
					}

					if ( $this->is_delayed_script( $matches_inline['attrs'] ) ) {
						return;
					}

					foreach ( $this->get_excluded_inline_content() as $excluded_content ) {
						if ( false !== strpos( $matches_inline['content'], $excluded_content ) ) {
							Logger::debug(
								'Inline script has excluded content.',
								[
									'js combine process',
									'excluded_content' => $excluded_content,
								]
							);
							return;
						}
					}

					foreach ( $this->get_move_after_inline_scripts() as $move_after_script ) {
						if ( false !== strpos( $matches_inline['content'], $move_after_script ) ) {
							$this->move_after[] = $script[0];
							return;
						}
					}

					$this->scripts[] = [
						'type'    => 'inline',
						'content' => $matches_inline['content'],
					];
				}

				return $script;
			},
			$scripts
		);

		return array_filter( $scripts );
	}

	/**
	 * Gets content for each script either from inline or from src
	 *
	 * @since 3.1
	 *
	 * @return string
	 */
	protected function get_content() {
		$content = '';

		foreach ( $this->scripts as $script ) {
			if ( 'file' === $script['type'] ) {
				$file_content = $this->get_file_content( $script['content'] );
				$content     .= $file_content;

				$this->add_to_minify( $file_content );
			} elseif ( 'url' === $script['type'] ) {
				$file_content = $this->local_cache->get_content( rocket_add_url_protocol( $script['content'] ) );
				$content     .= $file_content;

				$this->add_to_minify( $file_content );
			} elseif ( 'inline' === $script['type'] ) {
				$inline_js = rtrim( $script['content'], ";\n\t\r" ) . ';';
				$content  .= $inline_js;

				$this->add_to_minify( $inline_js );
			}
		}

		return $content;
	}

	/**
	 * Creates the minify URL if the minification is successful
	 *
	 * @since 2.11
	 *
	 * @param string $content Content to minify & combine.

	 * @return string|bool The minify URL if successful, false otherwise
	 */
	protected function combine( $content ) {
		if ( empty( $content ) ) {
			return false; // phpcs:ignore Universal.CodeAnalysis.ConstructorDestructorReturn.ReturnValueFound
		}

		$filename      = md5( $content . $this->minify_key ) . '.js';
		$minified_file = $this->minify_base_path . $filename;
		if ( ! rocket_direct_filesystem()->is_readable( $minified_file ) ) {
			$minified_content = $this->minify();

			if ( ! $minified_content ) {
				return false; // phpcs:ignore Universal.CodeAnalysis.ConstructorDestructorReturn.ReturnValueFound
			}

			$minify_filepath = $this->write_file( $minified_content, $minified_file );

			if ( ! $minify_filepath ) {
				return false; // phpcs:ignore Universal.CodeAnalysis.ConstructorDestructorReturn.ReturnValueFound
			}
		}

		return $this->get_minify_url( $filename ); // phpcs:ignore Universal.CodeAnalysis.ConstructorDestructorReturn.ReturnValueFound
	}

	/**
	 * Minifies the content
	 *
	 * @since 2.11
	 *
	 * @return string|bool Minified content, false if empty
	 */
	protected function minify() {
		$minified_content = $this->minifier->minify();

		if ( empty( $minified_content ) ) {
			return false;
		}

		return $minified_content;
	}

	/**
	 * Adds content to the minifier
	 *
	 * @since  3.1
	 *
	 * @param string $content Content to minify/combine.
	 * @return void
	 */
	protected function add_to_minify( $content ) {
		$this->minifier->add( $content );
	}

	/**
	 * Patterns in content excluded from being combined
	 *
	 * @since  3.1
	 *
	 * @return array
	 */
	protected function get_excluded_inline_content() {
		$excluded_inline = $this->options->get( 'exclude_inline_js', [] );

		/**
		 * Filters inline JS excluded from being combined
		 *
		 * @since 3.1
		 *
		 * @param array $pattern Patterns to match.
		 */
		return apply_filters( 'rocket_excluded_inline_js_content', $excluded_inline );
	}

	/**
	 * Patterns of inline JS to move after the combined JS file
	 *
	 * @since 3.1.4
	 *
	 * @return array
	 */
	protected function get_move_after_inline_scripts() {
		/**
		 * Filters inline JS to move after the combined JS file
		 *
		 * @since 3.1.4
		 *
		 * @param array $move_after_scripts Patterns to match.
		 */
		return apply_filters( 'rocket_move_after_combine_js', [] );
	}

	/**
	 * Gets all localized scripts data to exclude them from combine.
	 *
	 * @since 3.1.3
	 *
	 * @return array
	 */
	protected function get_localized_scripts() {
		static $localized_scripts;

		if ( isset( $localized_scripts ) ) {
			return $localized_scripts;
		}

		$localized_scripts = [];

		foreach ( array_unique( wp_scripts()->queue ) as $item ) {
			$data = wp_scripts()->print_extra_script( $item, false );

			if ( empty( $data ) ) {
				continue;
			}

			$localized_scripts[] = $data;
		}

		return $localized_scripts;
	}

	/**
	 * Is this script a delayed script or not.
	 *
	 * @since 3.7
	 *
	 * @param string $script_attributes Attributes beside the opening of script tag.
	 *
	 * @return bool True if it's a delayed script and false if not.
	 */
	private function is_delayed_script( $script_attributes ) {
		return false !== strpos( $script_attributes, 'data-rocketlazyloadscript=' );
	}

	/**
	 * Checks if the current URL is excluded from defer JS
	 *
	 * @since 3.8
	 *
	 * @param string $url URL to check.
	 * @return boolean
	 */
	private function is_defer_excluded( string $url ): bool {
		if (
			! empty( $this->excluded_defer_js )
			&&
			preg_match( '#(' . $this->excluded_defer_js . ')#i', $url )
		) {
			Logger::debug(
				'Script is excluded from defer JS.',
				[
					'js combine process',
					'url' => $url,
				]
			);
			return true;
		}

		return false;
	}
}
