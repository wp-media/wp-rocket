<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\Recommendations;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\RocketInsights\GlobalScore;
use WP_Rocket\Logger\LoggerAware;
use WP_Rocket\Logger\LoggerAwareInterface;

/**
 * Recommendations Data Manager.
 *
 * Centralized management for fetching, caching, and clearing recommendations.
 */
class DataManager implements LoggerAwareInterface {
	use LoggerAware;

	/**
	 * Transient name for storing recommendations.
	 *
	 * @var string
	 */
	private const TRANSIENT_NAME = 'wpr_ri_recommendations';

	/**
	 * Cache expiration time in seconds (24 hours).
	 *
	 * @var int
	 */
	private const CACHE_EXPIRATION = DAY_IN_SECONDS;

	/**
	 * Map of WP Rocket option keys to recommendation option slugs.
	 *
	 * These options affect recommendations and should trigger a refresh when changed.
	 *
	 * @var array<string, string>
	 */
	private const TRACKED_OPTIONS = [
		'image_dimensions'             => 'add_missing_image_dimensions',
		'defer_all_js'                 => 'deferred_js',
		'delay_js'                     => 'delay_js',
		'lazyload_css_bg_img'          => 'lazy_load_css',
		'lazyload_iframes'             => 'lazyload_iframes',
		'lazyload'                     => 'lazyload_images',
		'minify_css'                   => 'minify_css',
		'minify_js'                    => 'minify_js',
		'manual_preload'               => 'preload_cache',
		'auto_preload_fonts'           => 'auto_preload_fonts',
		'preload_links'                => 'preload_links',
		'remove_unused_css'            => 'rucss',
		'host_fonts_locally'           => 'selfhost_fonts',

		'performance_monitoring'       => 'performance_monitoring',
		'optimize_css_delivery'        => 'optimize_css_delivery',
		'delay_js_execution_safe_mode' => 'delay_js_execution_safe_mode',
		'lazyload_youtube'             => 'lazyload_youtube',
		'database_revisions'           => 'database_revisions',
		'database_auto_drafts'         => 'database_auto_drafts',
		'database_trashed_posts'       => 'database_trashed_posts',
		'database_spam_comments'       => 'database_spam_comments',
		'database_trashed_comments'    => 'database_trashed_comments',
		'database_optimize_tables'     => 'database_optimize_tables',
		'schedule_automatic_cleanup'   => 'schedule_automatic_cleanup',
		'cdn'                          => 'cdn',
		'control_heartbeat'            => 'control_heartbeat',
		'cache_ssl'                    => 'cache_ssl',
		'minify_google_fonts'          => 'minify_google_fonts',
		'emoji'                        => 'emoji',
		'async_css'                    => 'async_css',
		'cache_mobile'                 => 'cache_mobile',
		'do_caching_mobile_files'      => 'do_caching_mobile_files',
		'async_css_mobile'             => 'async_css_mobile',
		'cache_logged_user'            => 'cache_logged_user',
		'minify_concatenate_js'        => 'minify_concatenate_js',
		'database_all_transients'      => 'database_all_transients',
		'sucury_waf_cache_sync'        => 'sucury_waf_cache_sync',
		'varnish_auto_purge'           => 'varnish_auto_purge',
	];

	/**
	 * Recommendations API Client instance.
	 *
	 * @var APIClient
	 */
	private $api_client;

	/**
	 * Plugin options instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Global Score instance.
	 *
	 * @var GlobalScore
	 */
	private $global_score;

	/**
	 * Constructor.
	 *
	 * @param APIClient    $api_client   API Client instance.
	 * @param Options_Data $options      Options instance.
	 * @param GlobalScore  $global_score Global Score instance.
	 */
	public function __construct( APIClient $api_client, Options_Data $options, GlobalScore $global_score ) {
		$this->api_client   = $api_client;
		$this->options      = $options;
		$this->global_score = $global_score;
	}

	/**
	 * Get cached recommendations.
	 *
	 * @return array|false Recommendations data or false if not cached.
	 */
	public function get_recommendations() {
		$data = get_transient( self::TRANSIENT_NAME );

		if ( false === $data ) {
			$this->logger::debug( 'Recommendations: No cached data found' );
			return false;
		}

		// Validate structure.
		if ( ! isset( $data['status'] ) || ! isset( $data['timestamp'] ) ) {
			$this->logger::warning( 'Recommendations: Invalid cached data structure, clearing cache' );
			$this->clear_recommendations();
			return false;
		}

		$this->logger::debug(
			'Recommendations: Retrieved from cache',
			[
				'status'    => $data['status'],
				'timestamp' => $data['timestamp'],
			]
		);

		return $data;
	}

	/**
	 * Fetch recommendations from API and store in transient.
	 *
	 * @param array $options New settings to consider when fetching recommendations if available.
	 * @return bool True on success, false on failure.
	 */
	public function fetch_recommendations( array $options = [] ): bool {
		// Set loading status immediately.
		$this->set_loading_status();

		$this->logger::debug( 'Recommendations: Starting fetch from API' );

		// Get average metrics from global score data.
		$average_metrics = $this->get_average_metrics();

		// Get enabled WP Rocket options.
		$enabled_options = $this->get_enabled_options( $options );

		// Build API parameters.
		$params = [
			'email'           => $this->options->get( 'consumer_email', '' ),
			'language'        => $this->get_language(),
			'limit'           => 20, // Get all recommendations.
			'version'         => rocket_get_constant( 'WP_ROCKET_VERSION' ),
			'enabled_options' => $enabled_options,
		];

		if ( ! empty( $average_metrics ) ) {
			$params = array_merge( $params, $average_metrics );
		}

		// Add global score if available.
		$global_score = $average_metrics['global_score'] ?? null;
		if ( empty( $global_score ) ) {
			$global_score_data = $this->global_score->get_global_score_data();
			$global_score      = $global_score_data['score'] ?? null;
		}
		if ( ! empty( $global_score ) ) {
			$params['global_score'] = $global_score;
		}

		/**
		 * Filters the parameters sent to the Recommendations API.
		 *
		 * @param array $params API parameters.
		 * @return array Modified API parameters.
		 */
		$params = wpm_apply_filters_typed( 'array', 'rocket_insights_api_recommendations_params', $params );

		// Call API.
		$response = $this->api_client->get_recommendations( $params );

		// Handle error response.
		if ( is_wp_error( $response ) ) {
			$this->logger::error(
				'Recommendations: API request failed',
				[
					'code'    => $response->get_error_code(),
					'message' => $response->get_error_message(),
					'params'  => $params,
				]
			);

			$this->save_recommendations(
				[
					'status'          => 'failed',
					'recommendations' => [],
					'metadata'        => [],
					'timestamp'       => time(),
					'error'           => $response->get_error_message(),
				]
			);

			return false;
		}

		// Handle success response.
		if ( isset( $response['code'] ) && 200 === $response['code'] && isset( $response['data'] ) ) {
			$this->logger::info(
				'Recommendations: Successfully fetched from API',
				[
					'total' => count( $response['data']['recommendations'] ?? [] ),
				]
			);

			$this->save_recommendations(
				[
					'status'          => 'completed',
					'recommendations' => $response['data']['recommendations'] ?? [],
					'metadata'        => $response['data']['metadata'] ?? [],
					'timestamp'       => time(),
					'metrics_hash'    => $this->calculate_metrics_hash(),
				]
			);

			return true;
		}

		// Unexpected response format.
		$this->logger::error(
			'Recommendations: Unexpected API response format',
			[ 'response' => $response ]
		);

		$this->save_recommendations(
			[
				'status'          => 'failed',
				'recommendations' => [],
				'metadata'        => [],
				'timestamp'       => time(),
				'error'           => 'Unexpected API response format',
			]
		);

		return false;
	}

	/**
	 * Clear cached recommendations.
	 *
	 * @return void
	 */
	public function clear_recommendations(): void {
		delete_transient( self::TRANSIENT_NAME );

		$this->logger::debug( 'Recommendations: Cache cleared' );
	}

	/**
	 * Get list of WP Rocket option keys that affect recommendations.
	 *
	 * @return array<string> Array of option keys.
	 */
	public static function get_tracked_option_keys(): array {
		return array_keys( self::TRACKED_OPTIONS );
	}

	/**
	 * Get current recommendation status.
	 *
	 * @return string Status: 'expired', 'pending', 'loading', 'completed', 'failed'.
	 */
	public function get_status(): string {
		$data = $this->get_recommendations();

		if ( false === $data ) {
			return 'expired';
		}

		return $data['status'];
	}



	/**
	 * Check if required metrics are available for recommendations.
	 *
	 * @return bool True if metrics exist, false otherwise.
	 */
	public function has_required_metrics(): bool {
		$average_metrics = $this->get_average_metrics();

		if ( null === $average_metrics ) {
			return false;
		}

		// Verify core metrics exist.
		$required = [ 'lcp', 'ttfb', 'cls', 'tbt' ];
		foreach ( $required as $metric ) {
			if ( ! isset( $average_metrics[ $metric ] ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Determine if recommendations should be fetched.
	 *
	 * Compares current metrics hash with cached hash.
	 *
	 * @return bool True if should fetch, false if cache is valid.
	 */
	public function should_fetch_recommendations(): bool {
		$recommendations = $this->get_recommendations();

		// No cache = should fetch.
		if ( false === $recommendations ) {
			return true;
		}

		// Calculate current hash.
		$current_hash = $this->calculate_metrics_hash();
		$cached_hash  = $recommendations['metrics_hash'] ?? '';

		// Fetch if hash changed.
		return $current_hash !== $cached_hash;
	}

	/**
	 * Extend transient expiration without fetching.
	 *
	 * Used when data hasn't changed but transient is expiring.
	 *
	 * @return void
	 */
	public function extend_transient(): void {
		$data = $this->get_recommendations();

		if ( false === $data ) {
			return;
		}

		set_transient( self::TRANSIENT_NAME, $data, self::CACHE_EXPIRATION );

		$this->logger::debug( 'Recommendations: Transient extended (no changes detected)' );
	}

	/**
	 * Set loading status in transient.
	 *
	 * @return void
	 */
	private function set_loading_status(): void {
		$data = [
			'status'          => 'loading',
			'recommendations' => [],
			'metadata'        => [],
			'timestamp'       => time(),
		];

		set_transient( self::TRANSIENT_NAME, $data, self::CACHE_EXPIRATION );

		$this->logger::debug( 'Recommendations: Status set to loading' );
	}

	/**
	 * Save recommendations to transient.
	 *
	 * @param array $data Recommendations data.
	 * @return void
	 */
	private function save_recommendations( array $data ): void {
		set_transient( self::TRANSIENT_NAME, $data, self::CACHE_EXPIRATION );

		$this->logger::debug(
			'Recommendations: Saved to cache',
			[ 'status' => $data['status'] ]
		);
	}

	/**
	 * Calculate hash of current metrics and settings.
	 *
	 * @return string MD5 hash.
	 */
	private function calculate_metrics_hash(): string {
		$global_score_data = $this->global_score->get_global_score_data();
		$enabled_options   = $this->get_enabled_options();

		$data = [
			'score'           => $global_score_data['score'] ?? 0,
			'average_metrics' => $global_score_data['average_metrics'] ?? [],
			'enabled_options' => $enabled_options,
		];

		return md5( (string) wp_json_encode( $data ) );
	}

	/**
	 * Get average metrics from global score data (from Task 1.1).
	 *
	 * @return array|null Average metrics or null if not available.
	 */
	private function get_average_metrics(): ?array {
		$global_score_data = $this->global_score->get_global_score_data();

		if ( empty( $global_score_data['average_metrics'] ) ) {
			$this->logger::debug( 'Recommendations: No average metrics available' );
			return null;
		}

		return $global_score_data['average_metrics'];
	}

	/**
	 * Get enabled WP Rocket options.
	 *
	 * Returns array of option slugs that are currently enabled.
	 *
	 * @param array $options Optional array of new settings to check instead of current options.
	 * @return array Enabled option slugs.
	 */
	private function get_enabled_options( array $options = [] ): array {
		$enabled = [];

		foreach ( self::TRACKED_OPTIONS as $option_key => $option_slug ) {
			$value = $options[ $option_key ] ?? $this->options->get( $option_key, false );

			// Check if option is enabled.
			if ( $this->is_option_enabled( $option_key, $value ) ) {
				$enabled[] = $option_slug;
			}
		}

		$this->logger::debug(
			'Recommendations: Enabled options',
			[ 'enabled' => $enabled ]
		);

		return $enabled;
	}

	/**
	 * Check if a specific option is enabled.
	 *
	 * @param string $option_key Option key.
	 * @param mixed  $value      Option value.
	 * @return bool True if enabled, false otherwise.
	 */
	private function is_option_enabled( string $option_key, $value ): bool {
		// Boolean options.
		return ! empty( $value ) && 1 === (int) $value;
	}

	/**
	 * Get current language code.
	 *
	 * @return string ISO language code (e.g., 'en', 'fr').
	 */
	private function get_language(): string {
		// Get WordPress locale (e.g., 'en_US', 'fr_FR').
		$locale = get_locale();

		// Extract language code (first 2 characters).
		$language = substr( $locale, 0, 2 );

		return $language;
	}
}
