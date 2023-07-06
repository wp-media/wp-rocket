<?php
namespace WP_Rocket\Engine\Media\Lazyload\CSS;

use WP_Filesystem_Direct;
use WP_Post;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\Extractor;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\FileResolver;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\MappingFormatter;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\RuleFormatter;
use WP_Rocket\Engine\Media\Lazyload\CSS\Front\TagGenerator;
use WP_Rocket\Engine\Common\Cache\CacheInterface;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {

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
	 * WordPress filesystem.
	 *
	 * @var WP_Filesystem_Direct
	 */
	protected $filesystem;

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
	 * Instantiate class.
	 *
	 * @param Extractor                 $extractor Extract background images from CSS.
	 * @param RuleFormatter             $rule_formatter Format the CSS rule inside the CSS content.
	 * @param FileResolver              $file_resolver Resolves the name from the file from its URL.
	 * @param CacheInterface            $cache Cache instance.
	 * @param MappingFormatter          $mapping_formatter Format data for the Mapping file.
	 * @param TagGenerator              $tag_generator Generate tags from the mapping of lazyloaded images.
	 * @param WP_Filesystem_Direct|null $filesystem WordPress filesystem.
	 */
	public function __construct( Extractor $extractor, RuleFormatter $rule_formatter, FileResolver $file_resolver, CacheInterface $cache, MappingFormatter $mapping_formatter, TagGenerator $tag_generator, WP_Filesystem_Direct $filesystem = null ) {
		$this->extractor         = $extractor;
		$this->cache             = $cache;
		$this->rule_formatter    = $rule_formatter;
		$this->file_resolver     = $file_resolver;
		$this->filesystem        = $filesystem ?: rocket_direct_filesystem();
		$this->mapping_formatter = $mapping_formatter;
		$this->tag_generator     = $tag_generator;
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * The array key is the event name. The value can be:
	 *
	 *  * The method name
	 *  * An array with the method name and priority
	 *  * An array with the method name, priority and number of accepted arguments
	 *
	 * For instance:
	 *
	 *  * array('hook_name' => 'method_name')
	 *  * array('hook_name' => array('method_name', $priority))
	 *  * array('hook_name' => array('method_name', $priority, $accepted_args))
	 *  * array('hook_name' => array(array('method_name_1', $priority_1, $accepted_args_1)), array('method_name_2', $priority_2, $accepted_args_2)))
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_generate_lazyloaded_css' => [
				[ 'create_lazy_css_files', 13 ],
				[ 'create_lazy_inline_css', 14 ],
				[ 'add_lazy_tag', 15 ],
			],
			'rocket_buffer'                  => 'maybe_replace_css_images',
			'after_rocket_clean_domain'      => 'clear_generated_css',
			'after_rocket_clean_post'        => 'clear_generate_css_post',
			'wp_enqueue_scripts'             => 'insert_lazyload_script',
		];
	}

	/**
	 * Replace CSS images by the lazyloaded version.
	 *
	 * @param string $html page HTML.
	 * @return string
	 */
	public function maybe_replace_css_images( string $html ): string {
		$output = apply_filters(
			'rocket_generate_lazyloaded_css',
			[
				'html' => $html,
			]
			);

		if ( ! is_array( $output ) || ! key_exists( 'html', $output ) ) {
			return $html;
		}

		return $output['html'];
	}

	/**
	 * Clear the lazyload CSS files.
	 *
	 * @return void
	 */
	public function clear_generated_css() {
		$this->cache->clear();
	}

	/**
	 * Clear the lazyload CSS linked with a post.
	 *
	 * @param WP_Post $post post cleared.
	 * @return void
	 */
	public function clear_generate_css_post( WP_Post $post ) {
			$url = get_post_permalink( $post );
		if ( ! $url ) {
			return;
		}
			$this->cache->delete( $url );
	}

	/**
	 * Insert the lazyload script.
	 *
	 * @return void
	 */
	public function insert_lazyload_script() {
		wp_enqueue_script( 'rocket_lazyload_css', rocket_get_constant( 'WP_ROCKET_ASSETS_JS_URL' ) . 'lazyload-css.js', [], rocket_get_constant( 'WP_ROCKET_VERSION' ), true );
	}

	/**
	 * Create the lazyload file for CSS files.
	 *
	 * @param array $data Data sent.
	 * @return array
	 */
	public function create_lazy_css_files( array $data ): array {

		if ( ! key_exists( 'html', $data ) || ! key_exists( 'css_files', $data ) ) {
			return $data;
		}

		$html    = $data['html'];
		$mapping = [];

		foreach ( $data['css_files'] as $url ) {
			if ( ! $this->cache->has( $url ) ) {
				$mapping = $this->generate_css_file( $url );
				if ( empty( $mapping ) ) {
					continue;
				}
			} else {
				$mapping = array_merge( $mapping, $this->load_existing_mapping( $url ) );
			}

			$cached_url = $this->cache->generate_url( $url );

			$html = str_replace( $url, $cached_url, $html );
		}

		$data['html'] = $html;

		if ( ! key_exists( 'lazyloaded_images', $data ) ) {
			$data['lazyloaded_images'] = [];
		}

		$data['lazyloaded_images'] = array_merge( $data['lazyloaded_images'], $mapping );

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
			return $data;
		}

		$loaded = apply_filters( 'rocket_css_image_lazyload_images_load', [] );

		$tags = $this->tag_generator->generate( $data['lazyloaded_images'], $loaded );

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
			return [];
		}

		$content = $this->filesystem->get_contents( $path );

		if ( ! $content ) {
			return [];
		}

		$output = $this->generate_content( $content );
		if ( ! $this->cache->set( $url, $output['content'] ) ) {
			return [];
		}

		$this->cache->set( $url . '.json', json_encode( $output['urls'] ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode

		return $output['urls'];
	}

	/**
	 * Generate lazy content for a certain content.
	 *
	 * @param string $content Content to generate lazy for.
	 * @return array
	 */
	protected function generate_content( string $content ): array {
		$urls           = $this->extractor->extract( $content );
		$formatted_urls = [];
		foreach ( $urls as $url_tags ) {
			$url_tags       = array_map(
				function ( $url_tag ) {
					$url_tag['hash'] = wp_generate_uuid4();
					return $url_tag;
				},
				$url_tags
				);
			$content        = $this->rule_formatter->format( $content, $url_tags );
			$formatted_urls = array_merge( $formatted_urls, $this->mapping_formatter->format( $url_tags ) );
		}

		return [
			'urls'    => $formatted_urls,
			'content' => $content,
		];
	}

	/**
	 * Load existing mapping for a URL.
	 *
	 * @param string $url Url we load mapping for.
	 * @return array
	 */
	protected function load_existing_mapping( string $url ) {
		$content = $this->cache->get( $url . '.json' );
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
			return $data;
		}

		$html = $data['html'];

		$output = [
			'urls' => [],
		];

		foreach ( $data['css_inline'] as $content ) {
			$output = $this->generate_content( $content );

			if ( empty( $output ) ) {
				continue;
			}

			$html = str_replace( $content, $output['content'], $html );
		}

		$data['html'] = $html;

		if ( ! key_exists( 'lazyloaded_images', $data ) ) {
			$data['lazyloaded_images'] = [];
		}

		$data['lazyloaded_images'] = array_merge( $data['lazyloaded_images'], $output['urls'] );

		return $data;
	}
}
