<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Warmup;

use WP_Rocket\Engine\Optimization\AssetsLocalCache;
use WP_Rocket\Engine\Optimization\CSSTrait;
use WP_Rocket\Engine\Optimization\RegexTrait;
use WP_Rocket\Engine\Optimization\UrlTrait;
use WP_Rocket\Logger\Logger;
use WP_Rocket_WP_Async_Request;
use WP_Rocket\Admin\Options;

class ResourceFetcher extends WP_Rocket_WP_Async_Request {

	use RegexTrait, UrlTrait;

	/**
	 * Regex for stylesheets
	 */
	const LINK_PATTERN = '<link\s+(?:[^>]+[\s"\'])?href\s*=\s*[\'"]\s*(?<url>[^\'"\s]+)\s*?[\'"](?:[^>]+)?\/?>';

	/**
	 * Regex for scripts.
	 */
	const SCRIPT_PATTERN = '<script\s+(?:[^>]+[\s\'"])?src\s*=\s*[\'"]\s*?(?<url>[^\'"\s]+)\s*?[\'"](?:[^>]+)?\/?>';

	/**
	 * Prefix
	 *
	 * @var string
	 */
	protected $prefix = 'rocket';

	/**
	 * Action
	 *
	 * @var string
	 */
	protected $action = 'saas_warmup';

	/**
	 * Resources array.
	 *
	 * @var array
	 */
	private $resources = [];

	/**
	 * Assets local cache instance
	 *
	 * @var AssetsLocalCache
	 */
	private $local_cache;

	/**
	 * Resource fetcher process instance.
	 *
	 * @var ResourceFetcherProcess
	 */
	private $process;

	/**
	 * Excluded stylesheets patterns.
	 *
	 * @var string[]
	 */
	private $excluded_stylesheets = [
		'//fonts.googleapis.com/css',
	];

	/**
	 * Options API instance.
	 *
	 * @var Options
	 */
	private $options_api;

	/**
	 * ResourceFetcherProcess constructor.
	 *
	 * @param AssetsLocalCache       $local_cache Local cache instance.
	 * @param ResourceFetcherProcess $process     Resource fetcher process instance.
	 * @param Options                $options_api Options API instance.
	 */
	public function __construct( AssetsLocalCache $local_cache, ResourceFetcherProcess $process, Options $options_api ) {
		parent::__construct();

		$this->local_cache = $local_cache;
		$this->process     = $process;
		$this->options_api = $options_api;
	}

	/**
	 * Handle Collecting resources and save them into the DB
	 *
	 * @since 3.9
	 *
	 * @return void
	 */
	public function handle() {
		// Grab resources from page HTML.
		$html     = ! empty( $_POST['html'] ) ? wp_unslash( $_POST['html'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$is_error = ! empty( $_POST['is_error'] ) ? (bool) $_POST['is_error'] : false; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$page_url = ! empty( $_POST['page_url'] ) ? esc_url_raw( wp_unslash( $_POST['page_url'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$html = $this->decompress( $html );

		if ( empty( $html ) ) {
			$is_error = true;
		}

		$this->find_resources( $html, 'js' );
		$this->find_resources( $html, 'css' );

		if ( empty( $this->resources ) ) {
			$is_error = true;
		}

		// Send pages with error to background process to be saved into DB.
		if ( $is_error ) {
			$resource              = [];
			$resource['prewarmup'] = (int) ! empty( $_POST['prewarmup'] );// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$resource['page_url']  = $page_url;
			$resource['is_error']  = $is_error;

			$this->process->push_to_queue( $resource );
		}

		// Send resources to the background process to be saved into DB.
		foreach ( $this->resources as $resource ) {
			$resource['prewarmup'] = (int) ! empty( $_POST['prewarmup'] );// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$resource['page_url']  = $page_url;
			$resource['is_error']  = $is_error;

			$this->process->push_to_queue( $resource );
		}

		$this->process->save()->dispatch();
	}

	/**
	 * Find resources of type (css/js) on the page HTML.
	 *
	 * @since 3.9
	 *
	 * @param string $html Page HTML.
	 * @param string $type Specify to look for 'css' or 'js' resources.
	 *
	 * @return void
	 */
	private function find_resources( string $html, string $type ) {
		$pattern = ( 'css' === $type ) ? self::LINK_PATTERN : self::SCRIPT_PATTERN;

		$resources = $this->find( $pattern, $html );

		if ( empty( $resources ) ) {
			return;
		}

		foreach ( $resources as $resource ) {

			if ( 'css' === $type && ! $this->is_valid_stylesheet( $resource[0] ) ) {
				continue;
			}

			if ( 'js' === $type && ! $this->is_valid_script( $resource[0] ) ) {
				continue;
			}

			$external_url = $this->is_external_file( $resource['url'] );
			$path         = $this->get_url_path( $resource['url'], $external_url );

			$this->resources[ $path ] = [
				'url'      => $this->normalize_fullurl( $resource['url'], false ),
				'type'     => $type,
				'path'     => $path,
				'external' => $external_url,
			];

			if ( 'css' === $type ) {
				$this->resources[ $path ]['media'] = $this->get_stylesheet_media( $resource[0] );
			}
		}

	}

	/**
	 * Check that a link element is a stylesheet.
	 *
	 * @since 3.9
	 *
	 * @param string $link The link element to check.
	 *
	 * @return bool True for stylesheet; false for anything else.
	 */
	private function is_valid_stylesheet( string $link ) : bool {
		$valid_rel = $this->find( '(?:rel=[\'"]stylesheet[\'"])', $link, 'is' );
		if ( empty( $valid_rel ) ) {
			return false;
		}

		$exclusions = '';
		foreach ( $this->excluded_stylesheets as $excluded_item ) {
			$exclusions .= preg_quote( $excluded_item, '/' ) . '|';
		}

		$excluded = $this->find( rtrim( $exclusions, '|' ), $link );
		if ( ! empty( $excluded ) ) {
			return false;
		}

		return true;

	}

	/**
	 * Check if script is valid.
	 *
	 * @since 3.9
	 *
	 * @param string $script Script tag to be validated.
	 *
	 * @return bool
	 */
	private function is_valid_script( string $script ) : bool {
		return ! preg_match( '/(application\/ld\+json)|(application\/json)/i', $script );
	}

	/**
	 * Get media attribute from stylesheet.
	 *
	 * @param string $link Link HTML tag.
	 *
	 * @return string
	 */
	private function get_stylesheet_media( string $link ) : string {
		if (
			! preg_match( '/media\s*=\s*[\'"](?<media>.*)[\'"]/iUs', $link, $media_matches )
			||
			! isset( $media_matches['media'] )
		) {
			return 'all';
		}

		return $media_matches['media'];
	}

	/**
	 * Get url file path.
	 *
	 * @param string $url File url.
	 * @param bool   $external_url Is external url (true) or internal (false).
	 *
	 * @return string
	 */
	private function get_url_path( $url, bool $external_url ) : string {
		$file_path = $external_url ? $this->local_cache->get_filepath( $url ) : $this->get_file_path( $url );

		if ( empty( $file_path ) ) {
			Logger::error(
				'Couldn’t get the file path from the URL.',
				[
					'RUCSS warmup process',
					'url' => $url,
				]
			);

			return md5( uniqid() );
		}

		return $file_path;
	}

	/**
	 * Gets the CDN zones.
	 *
	 * @return array
	 */
	public function get_zones() : array {
		return [ 'all', 'css_and_js' ];
	}

	/**
	 * Compress the HTML data before sending it to the resources fetcher
	 *
	 * @since 3.9.2
	 *
	 * @param string $data HTML data.
	 *
	 * @return string
	 */
	public function compress( string $data ): string {
		if ( ! function_exists( 'gzencode' ) ) {
			return $data;
		}

		if ( empty( $data ) ) {
			return '';
		}

		$compressed = gzencode( $data, apply_filters( 'rocket_gzencode_level_compression', 6 ) );

		if ( false === $compressed ) {
			return $data;
		}

		return $compressed;
	}

	/**
	 * Decompress the HTML data received by the resources fetcher
	 *
	 * @since 3.9.2
	 *
	 * @param string $data Compressed HTML data.
	 *
	 * @return string
	 */
	private function decompress( string $data ): string {
		if ( ! function_exists( 'gzdecode' ) ) {
			return $data;
		}

		if ( empty( $data ) ) {
			return '';
		}

		$decompressed = @gzdecode( $data ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

		if ( false === $decompressed ) {
			return $data;
		}

		return $decompressed;
	}

	/**
	 * Cancel resource fetcher process
	 *
	 * @since 3.10
	 *
	 * @return void
	 */
	public function cancel_resource_fetcher_process() {
		$this->process->cancel_process();
	}
}
