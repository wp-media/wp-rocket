<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Preload\Abilities;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Abilities\AbilitiesInterface;
use WP_Rocket\Engine\Preload\Database\Queries\Cache as CacheQuery;
use WP_Rocket\Engine\Tracking\TrackingTrait;

class CheckCacheStatus implements AbilitiesInterface {
	use TrackingTrait;

	/**
	 * Options data instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Preload cache query instance.
	 *
	 * @var CacheQuery
	 */
	private $query;

	/**
	 * Constructor.
	 *
	 * @param Options_Data $options Options data instance.
	 * @param CacheQuery   $query   Preload cache query instance.
	 */
	public function __construct( Options_Data $options, CacheQuery $query ) {
		$this->options = $options;
		$this->query   = $query;
	}

	/**
	 * Registers the ability to get the Preload cache status for a URL, post, or term.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		wp_register_ability(
			'wp-rocket/get-cache-status',
			[
				'label'               => __( 'Get WP Rocket cache status', 'rocket' ),
				'description'         => _x(
					'Returns the Preload queue status for one URL, post, or term: tracked, status (pending, in-progress, completed, or failed), modified, and last_accessed.
Provide exactly one of url, post_id, or term_id together with taxonomy. This reports Preload queue tracking, not a raw filesystem check.
If tracked is false, tell the user the URL is not currently tracked by Preload (tracking may be disabled, or the URL was excluded or not crawled yet) rather than implying the page is not cached.',
					'Ability description',
					'rocket'
					),
				'category'            => 'wp-rocket-cache',
				'input_schema'        => [
					'type'       => 'object',
					'properties' => [
						'url'      => [
							'type'   => 'string',
							'format' => 'uri',
						],
						'post_id'  => [
							'type' => 'integer',
						],
						'term_id'  => [
							'type' => 'integer',
						],
						'taxonomy' => [
							'type' => 'string',
						],
					],
				],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'resolved_url'  => [
							'type' => [ 'string', 'null' ],
						],
						'tracked'       => [
							'type' => 'boolean',
						],
						'status'        => [
							'type' => [ 'string', 'null' ],
							'enum' => [ 'pending', 'in-progress', 'completed', 'failed', null ],
						],
						'modified'      => [
							'type' => [ 'string', 'null' ],
						],
						'last_accessed' => [
							'type' => [ 'string', 'null' ],
						],
						'note'          => [
							'type' => [ 'string', 'null' ],
						],
						'error'         => [
							'type' => [ 'string', 'null' ],
						],
					],
				],
				'execute_callback'    => [ $this, 'execute' ],
				'permission_callback' => [ $this, 'check_permissions' ],
				'meta'                => [
					'show_in_rest' => true,
					'mcp'          => [
						'public' => true,
					],
					'annotations'  => [
						'readonly'    => true,
						'destructive' => false,
						'idempotent'  => true,
					],
				],
			]
		);
	}

	/**
	 * Checks if the current user has permission to get the cache status.
	 *
	 * @return bool
	 */
	public function check_permissions(): bool {
		return current_user_can( 'rocket_manage_options' );
	}

	/**
	 * Executes the ability to get the Preload cache status for a URL, post, or term.
	 *
	 * @param array|null $input Input containing url, or post_id, or term_id + taxonomy.
	 *
	 * @return array
	 */
	public function execute( $input = null ): array {
		$this->track_event(
			'MCP Ability Executed',
			[
				'ability' => 'wp-rocket/get-cache-status',
				'context' => 'wp_plugin_mcp',
			]
		);

		$input = (array) $input;

		$validation_error = $this->validate_input( $input );

		if ( null !== $validation_error ) {
			return $this->error_response( $validation_error );
		}

		$resolved_url = $this->resolve_url( $input );

		if ( null === $resolved_url ) {
			return $this->error_response( __( 'Could not resolve a URL for the given identifier.', 'rocket' ) );
		}

		$tracking_enabled = (bool) $this->options->get( 'manual_preload', 0 );
		$lookup_url       = $this->normalize_url_scheme_and_host( $resolved_url );
		$rows             = $tracking_enabled ? $this->query->get_rows_by_url( $lookup_url ) : false;

		if ( false === $rows || empty( $rows ) ) {
			return [
				'resolved_url'  => $resolved_url,
				'tracked'       => false,
				'status'        => null,
				'modified'      => null,
				'last_accessed' => null,
				'note'          => $tracking_enabled
					? __( 'This URL is not currently tracked by Preload. It may be excluded from preloading, or it has not been crawled yet.', 'rocket' )
					: __( 'Preload tracking is disabled. Enable "Preload Cache" in Settings > WP Rocket > Preload to track cache status.', 'rocket' ),
				'error'         => null,
			];
		}

		$row = reset( $rows );

		return [
			'resolved_url'  => $resolved_url,
			'tracked'       => true,
			'status'        => $row->status,
			'modified'      => $row->modified ? gmdate( 'Y-m-d\TH:i:s\Z', $row->modified ) : null,
			'last_accessed' => $row->last_accessed ? gmdate( 'Y-m-d\TH:i:s\Z', $row->last_accessed ) : null,
			'note'          => null,
			'error'         => null,
		];
	}

	/**
	 * Validates that exactly one identifier was provided.
	 *
	 * @param array $input Input parameters.
	 *
	 * @return string|null Error message, or null when valid.
	 */
	private function validate_input( array $input ): ?string {
		$has_url      = ! empty( $input['url'] );
		$has_post_id  = ! empty( $input['post_id'] );
		$has_term_id  = ! empty( $input['term_id'] );
		$has_taxonomy = ! empty( $input['taxonomy'] );

		if ( $has_term_id && ! $has_taxonomy ) {
			return __( 'The taxonomy parameter is required when term_id is provided.', 'rocket' );
		}

		$identifiers_count = (int) $has_url + (int) $has_post_id + (int) $has_term_id;

		if ( 1 !== $identifiers_count ) {
			return __( 'Provide exactly one of url, post_id, or term_id+taxonomy.', 'rocket' );
		}

		return null;
	}

	/**
	 * Resolves the URL to look up from the given identifier.
	 *
	 * @param array $input Input parameters.
	 *
	 * @return string|null
	 */
	private function resolve_url( array $input ): ?string {
		if ( ! empty( $input['url'] ) ) {
			return untrailingslashit( rocket_add_url_protocol( (string) $input['url'] ) );
		}

		if ( ! empty( $input['post_id'] ) ) {
			$permalink = get_permalink( (int) $input['post_id'] );

			return $permalink ? untrailingslashit( $permalink ) : null;
		}

		if ( ! empty( $input['term_id'] ) ) {
			$term_link = get_term_link( (int) $input['term_id'], (string) ( $input['taxonomy'] ?? '' ) );

			if ( is_wp_error( $term_link ) || empty( $term_link ) ) {
				return null;
			}

			return untrailingslashit( $term_link );
		}

		return null;
	}

	/**
	 * Normalizes the resolved URL's scheme and host against the site's canonical home_url()
	 * when they refer to the same site, so a scheme (http/https) or www-prefix mismatch does
	 * not cause a false "not tracked" result.
	 *
	 * `Cache::get_rows_by_url()` strips the query string and trailing slash, but does not
	 * normalize scheme or a `www.` prefix, so this normalization happens before the lookup.
	 *
	 * @param string $url Resolved URL to normalize.
	 *
	 * @return string
	 */
	private function normalize_url_scheme_and_host( string $url ): string {
		$home_url    = home_url();
		$home_scheme = (string) wp_parse_url( $home_url, PHP_URL_SCHEME );
		$home_host   = (string) wp_parse_url( $home_url, PHP_URL_HOST );
		$url_host    = (string) wp_parse_url( $url, PHP_URL_HOST );

		if ( '' === $url_host || '' === $home_host ) {
			return $url;
		}

		$strip_www = static function ( string $host ): string {
			return preg_replace( '/^www\./i', '', $host ) ?? $host;
		};

		if ( $strip_www( $url_host ) !== $strip_www( $home_host ) ) {
			// Not the same site; leave untouched.
			return $url;
		}

		if ( '' !== $home_scheme ) {
			$url = set_url_scheme( $url, $home_scheme );
		}

		if ( $url_host !== $home_host ) {
			$url = str_replace( '://' . $url_host, '://' . $home_host, $url );
		}

		return $url;
	}

	/**
	 * Builds the error response shape.
	 *
	 * @param string $message Error message.
	 *
	 * @return array
	 */
	private function error_response( string $message ): array {
		return [
			'resolved_url'  => null,
			'tracked'       => false,
			'status'        => null,
			'modified'      => null,
			'last_accessed' => null,
			'note'          => null,
			'error'         => $message,
		];
	}
}
