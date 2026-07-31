<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Preload\Abilities;

use WP_Post;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Abilities\AbilitiesInterface;
use WP_Rocket\Engine\Tracking\TrackingTrait;

class PurgeCache implements AbilitiesInterface {
	use TrackingTrait;

	/**
	 * Options data instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Constructor.
	 *
	 * @param Options_Data $options Options data instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Registers the ability to clear the WP Rocket cache for a URL, post, or the whole domain.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		wp_register_ability(
			'wp-rocket/clear-cache',
			[
				'label'               => __( 'Clear WP Rocket cache', 'rocket' ),
				'description'         => _x(
					'Clears the WP Rocket cache for a single URL, a single post, or the entire domain. Requires scope (url, post, or domain), plus url or post_id matching the scope.
This always succeeds when dispatched, even if no problem was detected with the cache; there is no precondition check.
cloudflare_purge_triggered indicates whether Cloudflare cache was also purged: true for post/domain scope when Cloudflare integration is on, false for url scope (which never purges Cloudflare), null when Cloudflare integration is off.
Confirm with the user before calling this for scope=domain, since it clears the cache for the entire site.',
					'Ability description',
					'rocket'
					),
				'category'            => 'wp-rocket-cache',
				'input_schema'        => [
					'type'       => 'object',
					'properties' => [
						'scope'   => [
							'type' => 'string',
							'enum' => [ 'url', 'post', 'domain' ],
						],
						'url'     => [
							'anyOf' => [
								[ 'type' => 'string' ],
								[
									'type'  => 'array',
									'items' => [ 'type' => 'string' ],
								],
							],
						],
						'post_id' => [
							'type' => 'integer',
						],
						'lang'    => [
							'type' => 'string',
						],
					],
					'required'   => [ 'scope' ],
				],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'accepted'                   => [
							'type' => 'boolean',
						],
						'scope'                      => [
							'type' => 'string',
						],
						'cleared_urls'               => [
							'type'  => [ 'array', 'null' ],
							'items' => [ 'type' => 'string' ],
						],
						'cloudflare_purge_triggered' => [
							'type' => [ 'boolean', 'null' ],
						],
						'message'                    => [
							'type' => 'string',
						],
						'error'                      => [
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
						'readonly'    => false,
						'destructive' => true,
						'idempotent'  => true,
					],
				],
			]
		);
	}

	/**
	 * Checks if the current user has permission to clear the cache.
	 *
	 * @return bool
	 */
	public function check_permissions(): bool {
		return current_user_can( 'rocket_purge_cache' );
	}

	/**
	 * Executes the ability to clear the WP Rocket cache.
	 *
	 * @param array|null $input Input containing scope, and url or post_id or lang depending on scope.
	 *
	 * @return array
	 */
	public function execute( $input = null ): array {
		$this->track_event(
			'MCP Ability Executed',
			[
				'ability' => 'wp-rocket/clear-cache',
				'context' => 'wp_plugin_mcp',
			]
		);

		$input = (array) $input;
		$scope = isset( $input['scope'] ) ? (string) $input['scope'] : '';

		switch ( $scope ) {
			case 'url':
				return $this->clear_url_scope( $input );

			case 'post':
				return $this->clear_post_scope( $input );

			case 'domain':
				return $this->clear_domain_scope( $input );

			default:
				return $this->error_response( $scope, __( 'Invalid or missing scope. Must be one of: url, post, domain.', 'rocket' ) );
		}
	}

	/**
	 * Clears the cache for one or more raw URLs.
	 *
	 * @param array $input Input parameters.
	 *
	 * @return array
	 */
	private function clear_url_scope( array $input ): array {
		if ( empty( $input['url'] ) ) {
			return $this->error_response( 'url', __( 'The url parameter is required when scope is "url".', 'rocket' ) );
		}

		$urls = array_values( array_filter( array_map( 'strval', (array) $input['url'] ) ) );

		if ( empty( $urls ) ) {
			return $this->error_response( 'url', __( 'The url parameter is required when scope is "url".', 'rocket' ) );
		}

		foreach ( $urls as $url ) {
			if ( ! $this->is_url_in_scope( $url ) ) {
				return $this->error_response(
					'url',
					sprintf(
						/* translators: %s: URL */
						__( 'The URL %s does not belong to this site and cannot be cleared.', 'rocket' ),
						$url
					)
				);
			}
		}

		$homepage_urls = array_values( array_filter( $urls, [ $this, 'is_homepage_url' ] ) );
		$other_urls    = array_values( array_diff( $urls, $homepage_urls ) );

		if ( ! empty( $homepage_urls ) ) {
			// Replicates the homepage special-case from do_admin_post_rocket_purge_cache().
			rocket_clean_home();
		}

		if ( ! empty( $other_urls ) ) {
			rocket_clean_files( $other_urls );
		}

		return [
			'accepted'                   => true,
			'scope'                      => 'url',
			'cleared_urls'               => $urls,
			'cloudflare_purge_triggered' => $this->cloudflare_purge_triggered_for( 'url' ),
			'message'                    => __( 'Cache clear request accepted for the given URL(s).', 'rocket' ),
			'error'                      => null,
		];
	}

	/**
	 * Clears the cache for a single post.
	 *
	 * @param array $input Input parameters.
	 *
	 * @return array
	 */
	private function clear_post_scope( array $input ): array {
		if ( empty( $input['post_id'] ) ) {
			return $this->error_response( 'post', __( 'The post_id parameter is required when scope is "post".', 'rocket' ) );
		}

		$post_id = (int) $input['post_id'];
		$post    = get_post( $post_id );

		if ( ! $post instanceof WP_Post ) {
			return $this->error_response( 'post', __( 'The requested post could not be found.', 'rocket' ) );
		}

		$permalink = get_permalink( $post_id );

		rocket_clean_post( $post_id );

		return [
			'accepted'                   => true,
			'scope'                      => 'post',
			'cleared_urls'               => $permalink ? [ $permalink ] : [],
			'cloudflare_purge_triggered' => $this->cloudflare_purge_triggered_for( 'post' ),
			'message'                    => __( 'Cache clear request accepted for the given post.', 'rocket' ),
			'error'                      => null,
		];
	}

	/**
	 * Clears the cache for the entire domain.
	 *
	 * @param array $input Input parameters.
	 *
	 * @return array
	 */
	private function clear_domain_scope( array $input ): array {
		$lang = isset( $input['lang'] ) ? (string) $input['lang'] : '';

		rocket_clean_domain( $lang );

		return [
			'accepted'                   => true,
			'scope'                      => 'domain',
			'cleared_urls'               => null,
			'cloudflare_purge_triggered' => $this->cloudflare_purge_triggered_for( 'domain' ),
			'message'                    => __( 'Cache clear request accepted for the entire domain.', 'rocket' ),
			'error'                      => null,
		];
	}

	/**
	 * Checks whether a URL belongs to the current site.
	 *
	 * Path-scoped (not just host-scoped) so that, on a subdirectory multisite install, a
	 * URL belonging to a sibling subsite sharing the same host is correctly rejected.
	 *
	 * @param string $url URL to check.
	 *
	 * @return bool
	 */
	private function is_url_in_scope( string $url ): bool {
		$home = untrailingslashit( home_url() );
		$url  = untrailingslashit( $url );

		return 0 === strpos( $url, $home );
	}

	/**
	 * Checks whether a URL is the site's homepage.
	 *
	 * @param string $url URL to check.
	 *
	 * @return bool
	 */
	private function is_homepage_url( string $url ): bool {
		return untrailingslashit( $url ) === untrailingslashit( home_url() );
	}

	/**
	 * Determines whether Cloudflare's cache is also purged for the given scope.
	 *
	 * `rocket_clean_post()`/`rocket_clean_domain()` fire `after_rocket_clean_post`/
	 * `rocket_after_clean_domain`, which the Cloudflare Subscriber listens to.
	 * `rocket_clean_files()` alone (used for raw URL scope) does not fire either hook.
	 *
	 * @param string $scope Scope being cleared (url, post, domain).
	 *
	 * @return bool|null True/false when Cloudflare integration is enabled, null when it is disabled (not applicable).
	 */
	private function cloudflare_purge_triggered_for( string $scope ): ?bool {
		$cloudflare_enabled = (bool) $this->options->get( 'do_cloudflare', 0 );

		if ( ! $cloudflare_enabled ) {
			return null;
		}

		return 'url' !== $scope;
	}

	/**
	 * Builds the error response shape.
	 *
	 * @param string $scope   Scope being cleared, or empty/invalid value received.
	 * @param string $message Error message.
	 *
	 * @return array
	 */
	private function error_response( string $scope, string $message ): array {
		return [
			'accepted'                   => false,
			'scope'                      => $scope,
			'cleared_urls'               => null,
			'cloudflare_purge_triggered' => null,
			'message'                    => $message,
			'error'                      => $message,
		];
	}
}
