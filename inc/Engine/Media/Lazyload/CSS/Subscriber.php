<?php
namespace WP_Rocket\Engine\Media\Lazyload\CSS;

use WP_Filesystem_Direct;
use WP_Post;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Media\Lazyload\CSS\Data\LazyloadedContent;
use WP_Rocket\Engine\Media\Lazyload\CSS\Data\LazyloadCSSContentFactory;
use WP_Rocket\Engine\Media\Lazyload\CSS\Data\ProtectedContent;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\{ContentFetcher,
	Extractor,
	FileResolver,
	MappingFormatter,
	RuleFormatter,
	TagGenerator};
use WP_Rocket\Engine\Common\Cache\CacheInterface;
use WP_Rocket\Engine\Optimization\RegexTrait;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Logger\LoggerAware;
use WP_Rocket\Logger\LoggerAwareInterface;

class Subscriber implements Subscriber_Interface, LoggerAwareInterface {
	use LoggerAware;
	use RegexTrait;

	/**
	 * Extract background images from CSS.
	 *
	 * @var Extractor
	 */
	protected $extractor;

	/**
	 * Cache instance.
	 *
	 * @var CacheInterface
	 */
	protected $cache;

	/**
	 * Format the CSS rule inside the CSS content.
	 *
	 * @var RuleFormatter
	 */
	protected $rule_formatter;

	/**
	 * Resolves the name from the file from its URL.
	 *
	 * @var FileResolver
	 */
	protected $file_resolver;

	/**
	 * Format data for the Mapping file.
	 *
	 * @var MappingFormatter
	 */
	protected $mapping_formatter;

	/**
	 * Generate tags from the mapping of lazyloaded images.
	 *
	 * @var TagGenerator
	 */
	protected $tag_generator;

	/**
	 * Fetch content.
	 *
	 * @var ContentFetcher
	 */
	protected $fetcher;

	/**
	 * Context.
	 *
	 * @var ContextInterface
	 */
	protected $context;

	/**
	 * WPR Options.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * Make LazyloadedContent instance.
	 *
	 * @var LazyloadCSSContentFactory
	 */
	protected $lazyloaded_content_factory;

	/**
	 * WordPress filesystem.
	 *
	 * @var WP_Filesystem_Direct
	 */
	protected $filesystem;

	/**
	 * Instantiate class.
	 *
	 * @param Extractor                 $extractor Extract background images from CSS.
	 * @param RuleFormatter             $rule_formatter Format the CSS rule inside the CSS content.
	 * @param FileResolver              $file_resolver Resolves the name from the file from its URL.
	 * @param CacheInterface            $cache Cache instance.
	 * @param MappingFormatter          $mapping_formatter Format data for the Mapping file.
	 * @param TagGenerator              $tag_generator Generate tags from the mapping of lazy loaded images.
	 * @param ContentFetcher            $fetcher Fetch content.
	 * @param ContextInterface          $context Context.
	 * @param Options_Data              $options WPR Options.
	 * @param LazyloadCSSContentFactory $lazyloaded_content_factory Make LazyloadedContent instance.
	 * @param WP_Filesystem_Direct|null $filesystem WordPress filesystem.
	 */
	public function __construct( Extractor $extractor, RuleFormatter $rule_formatter, FileResolver $file_resolver, CacheInterface $cache, MappingFormatter $mapping_formatter, TagGenerator $tag_generator, ContentFetcher $fetcher, ContextInterface $context, Options_Data $options, LazyloadCSSContentFactory $lazyloaded_content_factory, WP_Filesystem_Direct $filesystem = null ) {
		$this->extractor                  = $extractor;
		$this->cache                      = $cache;
		$this->rule_formatter             = $rule_formatter;
		$this->file_resolver              = $file_resolver;
		$this->mapping_formatter          = $mapping_formatter;
		$this->tag_generator              = $tag_generator;
		$this->context                    = $context;
		$this->options                    = $options;
		$this->fetcher                    = $fetcher;
		$this->lazyloaded_content_factory = $lazyloaded_content_factory;
		$this->filesystem                 = $filesystem ?: rocket_direct_filesystem();
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_generate_lazyloaded_css'        => [
				[ 'create_lazy_css_files', 18 ],
				[ 'create_lazy_inline_css', 21 ],
				[ 'add_lazy_tag', 24 ],
			],
			'rocket_buffer'                         => [ 'maybe_replace_css_images', 1002 ],
			'rocket_after_clean_domain'             => 'clear_generated_css',
			'wp_enqueue_scripts'                    => 'insert_lazyload_script',
			'rocket_css_image_lazyload_images_load' => [ 'exclude_rocket_lazyload_excluded_src', 10, 2 ],
			'rocket_lazyload_css_ignored_urls'      => 'remove_svg_from_lazyload_css',
		];
	}

	/**
	 * Replace CSS images by the lazyloaded version.
	 *
	 * @param string $html page HTML.
	 * @return string
	 */
	public function maybe_replace_css_images( string $html ): string {

		if ( ! $this->context->is_allowed() ) {
			return $html;
		}

		$this->logger::debug(
			'Starting lazyload',
			$this->generate_log_context(
				[
					'data' => $html,
				]
				)
			);

		/**
		 * Generate lazyload CSS for the page.
		 *
		 * @param array $data Data passed to generate the lazyload CSS.
		 */
		$output = wpm_apply_filters_typed(
			'array',
			'rocket_generate_lazyloaded_css',
			[
				'html' => $html,
			]
		);

		if ( ! key_exists( 'html', $output ) ) {
			$this->logger::debug(
				'Lazyload bailed out',
				$this->generate_log_context(
					[
						'data' => $html,
					]
					)
				);
			return $html;
		}

		$this->logger::debug(
			'Ending lazyload',
			$this->generate_log_context(
				[
					'data' => $html,
				]
				)
			);

		return $output['html'];
	}

	/**
	 * Clear the lazyload CSS files.
	 *
	 * @return void
	 */
	public function clear_generated_css() {
		$this->logger::debug(
			'Clear lazy CSS',
			$this->generate_log_context()
		);
		$this->cache->clear();
	}

	/**
	 * Insert the lazyload script.
	 *
	 * @return void
	 */
	public function insert_lazyload_script() {
		if ( ! $this->context->is_allowed() ) {
			return;
		}

		/**
		 * Filters the threshold at which lazyload is triggered
		 *
		 * @since 1.2
		 *
		 * @param int $threshold Threshold value.
		 */
		$threshold = (int) apply_filters( 'rocket_lazyload_threshold', 300 );

		$script_path = rocket_get_constant( 'WP_ROCKET_ASSETS_JS_PATH' ) . 'lazyload-css.min.js';

		if ( ! $this->filesystem->exists( $script_path ) ) {
			return;
		}

		$content = $this->filesystem->get_contents( $script_path );

		if ( ! $content ) {
			return;
		}

		wp_register_script( 'rocket_lazyload_css', '', [], false, true ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NoExplicitVersion
		wp_enqueue_script( 'rocket_lazyload_css' );
		wp_add_inline_script( 'rocket_lazyload_css', $content, 'after' );
		wp_localize_script(
			'rocket_lazyload_css',
			'rocket_lazyload_css_data',
			[
				'threshold' => $threshold,
			]
			);
	}

	/**
	 * Create the lazyload file for CSS files.
	 *
	 * @param array $data Data sent.
	 * @return array
	 */
	public function create_lazy_css_files( array $data ): array {
		if ( ! key_exists( 'html', $data ) || ! key_exists( 'css_files', $data ) ) {
			$this->logger::debug(
				'Create lazy css files bailed out',
				$this->generate_log_context(
					[
						'data' => $data,
					]
					)
			);
			return $data;
		}

		$html    = $data['html'];
		$html    = $this->replace_html_comments( $html );
		$mapping = [];

		$css_files = array_unique( $data['css_files'] );

		$protected_content = $this->protect_css_urls( $html, $css_files );

		$html              = $protected_content->get_content();
		$css_files_mapping = $protected_content->get_protected_files_mapping();

		foreach ( $css_files as $url ) {

			if ( $this->is_excluded( $url ) ) {
				$this->logger::debug(
					"Excluded lazy css files $url",
					$this->generate_log_context()
				);
				continue;
			}

			$url_key = $this->format_url( $url );
			if ( ! $this->cache->has( $url_key ) ) {
				$this->logger::debug(
					"Generate lazy css files $url",
					$this->generate_log_context()
					);

				$file_mapping = $this->generate_css_file( $url );
				if ( empty( $file_mapping ) ) {
					$this->logger::debug(
						"Create lazy css files $url bailed out",
						$this->generate_log_context()
						);
					continue;
				}

				$mapping = array_merge( $mapping, $file_mapping );

			} else {
				$this->logger::debug(
					"Load lazy css files $url",
					$this->generate_log_context()
					);
				$mapping = array_merge( $mapping, $this->load_existing_mapping( $url ) );
			}

			$cached_url = $this->generate_asset_url( $url );

			$html = str_replace( $css_files_mapping[ $url ], $cached_url, $html );
		}

		foreach ( $css_files_mapping as $url => $placeholder ) {
			$html = str_replace( $placeholder, $url, $html );
		}

		$html = $this->restore_html_comments( $html );

		$data['html'] = $html;

		if ( ! key_exists( 'lazyloaded_images', $data ) ) {
			$data['lazyloaded_images'] = [];
		}

		$data['lazyloaded_images'] = array_merge( $data['lazyloaded_images'], $mapping );
		$data['lazyloaded_images'] = array_unique( $data['lazyloaded_images'], SORT_REGULAR );

		return $data;
	}

	/**
	 * Add the lazy tag to the current HTML.
	 *
	 * @param array $data Data sent.
	 * @return array
	 */
	public function add_lazy_tag( array $data ): array {

		if ( ! key_exists( 'html', $data ) || ! key_exists( 'lazyloaded_images', $data ) ) {
			$this->logger::debug(
				'Add lazy tag bailed out',
				$this->generate_log_context(
					[
						'data' => $data,
					]
					)
				);
			return $data;
		}

		$lazyload_images = $data['lazyloaded_images'];

		/**
		 * Lazyload background CSS excluded urls.
		 *
		 * @param array $excluded Excluded URLs.
		 * @param array $urls List of Urls processed.
		 */
		$loaded = apply_filters( 'rocket_css_image_lazyload_images_load', [], $lazyload_images );

		$tags = $this->tag_generator->generate( $lazyload_images, $loaded );
		$this->logger::debug(
			'Add lazy tag generated',
			$this->generate_log_context(
				[
					'data' => $tags,
				]
				)
			);
		$data['html'] = str_replace( '</head>', "$tags</head>", $data['html'] );

		return $data;
	}

	/**
	 * Generate lazy CSS for a file.
	 *
	 * @param string $url Url from the CSS.
	 * @return array
	 */
	protected function generate_css_file( string $url ) {
		$path = $this->file_resolver->resolve( $url );

		if ( ! $path ) {
			$path = $url;
		}

		$content = $this->fetcher->fetch( $path, $this->cache->generate_path( $url ) );

		if ( ! $content ) {
			return [];
		}

		$output = $this->generate_content( $content, $this->cache->generate_url( $url ) );

		if ( count( $output->get_urls() ) === 0 ) {
			return [];
		}

		if ( ! $this->cache->set( $this->format_url( $url ), $output->get_content() ) ) {
			return [];
		}

		$this->cache->set( $this->get_mapping_file_url( $url ), json_encode( $output->get_urls() ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode

		return $output->get_urls();
	}

	/**
	 * Generate lazy content for a certain content.
	 *
	 * @param string $content Content to generate lazy for.
	 * @param string $url URL of the file we are extracting content from.
	 * @return LazyloadedContent
	 */
	protected function generate_content( string $content, string $url = '' ): LazyloadedContent {
		$urls           = $this->extractor->extract( $content, $url );
		$formatted_urls = [];
		foreach ( $urls as $url_tags ) {
			$url_tags       = $this->add_hashes( $url_tags );
			$content        = $this->rule_formatter->format( $content, $url_tags );
			$formatted_urls = array_merge( $formatted_urls, $this->mapping_formatter->format( $url_tags ) );
		}

		return $this->lazyloaded_content_factory->make_lazyloaded_content( $formatted_urls, $content );
	}

	/**
	 * Load existing mapping for a URL.
	 *
	 * @param string $url Url we load mapping for.
	 * @return array
	 */
	protected function load_existing_mapping( string $url ) {
		$content = $this->cache->get( $this->get_mapping_file_url( $url ) );
		$urls    = json_decode( $content, true );
		if ( ! $urls ) {
			return [];
		}
		return $urls;
	}

	/**
	 * Create the lazyload file for inline CSS.
	 *
	 * @param array $data Data sent.
	 * @return array
	 */
	public function create_lazy_inline_css( array $data ): array {

		if ( ! key_exists( 'html', $data ) || ! key_exists( 'css_inline', $data ) ) {
			$this->logger::debug(
				'Create lazy css inline bailed out',
				$this->generate_log_context(
					[
						'data' => $data,
					]
					)
				);
			return $data;
		}

		$html = $data['html'];

		if ( ! key_exists( 'lazyloaded_images', $data ) ) {
			$data['lazyloaded_images'] = [];
		}

		foreach ( $data['css_inline'] as $content ) {

			$output = $this->generate_content( $content );

			$html = str_replace( $content, $output->get_content(), $html );

			$data['lazyloaded_images'] = array_merge( $data['lazyloaded_images'], $output->get_urls() );
		}

		$data['html'] = $html;

		return $data;
	}

	/**
	 * Format a URL.
	 *
	 * @param string $url URL to format.
	 * @return string
	 */
	protected function format_url( string $url ): string {
		return strtok( $url, '?' );
	}

	/**
	 * Check of the string is excluded.
	 *
	 * @param string $string String to check.
	 * @return bool
	 */
	protected function is_excluded( string $string ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.stringFound

		$values = [
			$string,
		];

		$parsed_url_host = wp_parse_url( $string, PHP_URL_HOST );

		if ( ! $parsed_url_host ) {
			$values [] = rocket_get_home_url() . $string;
		}

		/**
		 * Filters the src used to prevent lazy load from being applied.
		 *
		 * @param array $excluded_src An array of excluded src.
		 */
		$excluded_values = wpm_apply_filters_typed( 'array', 'rocket_lazyload_excluded_src', [] );

		$excluded_values = array_filter( $excluded_values );

		if ( empty( $excluded_values ) ) {
			return false;
		}

		foreach ( $values as $value ) {
			foreach ( $excluded_values as $excluded_value ) {
				if ( strpos( $value, $excluded_value ) !== false ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Is the feature activated.
	 *
	 * @return bool
	 */
	protected function is_activated(): bool {
		return (bool) $this->options->get( 'lazyload_css_bg_img', false );
	}

	/**
	 * Add lazyload_excluded_src to excluded filters.
	 *
	 * @param array $excluded Excluded URLs.
	 * @param array $urls List of Urls processed.
	 * @return mixed
	 */
	public function exclude_rocket_lazyload_excluded_src( $excluded, $urls ) {

		/**
		 * Filters the src used to prevent lazy load from being applied.
		 *
		 * @param array $excluded_src An array of excluded src.
		 */
		$excluded_values = wpm_apply_filters_typed( 'array', 'rocket_lazyload_excluded_src', [] );

		$excluded_values = array_filter( $excluded_values );

		if ( empty( $excluded_values ) ) {
			return $excluded;
		}

		foreach ( $urls as $url ) {
			foreach ( $excluded_values as $excluded_value ) {
				if ( strpos( $url['selector'], $excluded_value ) !== false || strpos( $url['style'], $excluded_value ) !== false ) {
					$excluded[] = $url;
					break;
				}
			}
		}

		return $excluded;
	}

	/**
	 * Add hashes.
	 *
	 * @param array $tags Tags.
	 * @return array
	 */
	protected function add_hashes( array $tags ): array {
		return array_map(
			function ( $url_tag ) {
				/**
				 * Lazyload CSS hash.
				 *
				 * @param string $hash Lazyload CSS hash.
				 * @param mixed  $url_tag URL tag.
				 */
				$url_tag['hash'] = apply_filters( 'rocket_lazyload_css_hash',  wp_generate_uuid4(), $url_tag );
				return $url_tag;
			},
			$tags
		);
	}

	/**
	 * Return mapping file URL.
	 *
	 * @param string $url Resource URL.
	 * @return string
	 */
	protected function get_mapping_file_url( string $url ): string {
		return $this->format_url( $url ) . '.json';
	}

	/**
	 * Add timestamp to URL.
	 *
	 * @param string $url Asset Url.
	 *
	 * @return string
	 */
	protected function generate_asset_url( string $url ): string {
		$parsed_query = wp_parse_url( $url, PHP_URL_QUERY );
		$queries      = [];

		if ( $parsed_query ) {
			parse_str( $parsed_query, $queries );
		}

		$queries['wpr_t'] = current_time( 'timestamp' ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested

		$cached_url = $this->cache->generate_url( $this->format_url( $url ) );

		$this->logger::debug(
			"Generated url lazy css files $url",
			$this->generate_log_context(
				[
					'data' => $cached_url,
				]
				)
		);

		return add_query_arg( $queries, $cached_url );
	}

	/**
	 * Generate the context for logs.
	 *
	 * @param array $data Data to pass to logs.
	 * @return array
	 */
	protected function generate_log_context( array $data = [] ): array {
		return array_merge(
			$data,
			[
				'type' => 'lazyload_css_bg_images',
			]
			);
	}

	/**
	 * Protect URL inside the content.
	 *
	 * @param string $content Content to protect.
	 * @param array  $css_files CSS files from the content.
	 * @return ProtectedContent
	 */
	protected function protect_css_urls( string $content, array $css_files ): ProtectedContent {
		usort(
			$css_files,
			function ( $url1, $url2 ) {
				return strlen( $url1 ) < strlen( $url2 ) ? 1 : -1;
			}
		);

		$css_files_mapping = [];

		foreach ( $css_files as $url ) {
			$placeholder = uniqid( 'url_bg_css_' );

			$content = str_replace( $url, $placeholder, $content );

			$css_files_mapping[ $url ] = $placeholder;
		}

		return $this->lazyloaded_content_factory->make_protected_content( $css_files_mapping, $content );
	}

	/**
	 * Exclude SVG from lazy loaded images.
	 *
	 * @param array $urls URLs to exclude.
	 * @return array
	 */
	public function remove_svg_from_lazyload_css( array $urls ): array {
		$urls[] = 'data:image/svg+xml';

		return $urls;
	}
}
