<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\Abilities;

use WP_Rocket\Engine\Abilities\AbilitiesInterface;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\DataManager;

class GetRecommendations implements AbilitiesInterface {

	/**
	 * Option slugs that are both tracked by Rocket Insights (DataManager::TRACKED_OPTIONS)
	 * and settable via the MCP set-option ability (SetOption allowed options).
	 *
	 * Derived from: intersection of SetOption allowed options (SetOption.php)
	 * and DataManager::TRACKED_OPTIONS keys (DataManager.php).
	 * Update this list whenever either source file changes.
	 * Excluded from TRACKED_OPTIONS: performance_monitoring, plugin_rocketcdn, plugin_imagify.
	 */
	private const MCP_ACTIONABLE_OPTION_SLUGS = [
		'image_dimensions',
		'defer_all_js',
		'delay_js',
		'lazyload_css_bg_img',
		'lazyload_iframes',
		'lazyload',
		'minify_css',
		'minify_js',
		'manual_preload',
		'auto_preload_fonts',
		'preload_links',
		'remove_unused_css',
		'host_fonts_locally',
		'optimize_css_delivery',
		'delay_js_execution_safe_mode',
		'lazyload_youtube',
		'database_revisions',
		'database_auto_drafts',
		'database_trashed_posts',
		'database_spam_comments',
		'database_trashed_comments',
		'database_optimize_tables',
		'schedule_automatic_cleanup',
		'cdn',
		'control_heartbeat',
		'cache_logged_user',
		'minify_concatenate_js',
		'database_all_transients',
		'sucury_waf_cache_sync',
		'varnish_auto_purge',
	];

	/**
	 * Recommendations Data Manager instance.
	 *
	 * @var DataManager
	 */
	private $data_manager;

	/**
	 * Constructor.
	 *
	 * @param DataManager $data_manager The data manager for recommendations.
	 */
	public function __construct( DataManager $data_manager ) {
		$this->data_manager = $data_manager;
	}

	/**
	 * Registers the ability to get Rocket Insights recommendations.
	 */
	public function register(): void {
		wp_register_ability(
			'wp-rocket/get-recommendations',
			[
				'label'               => __( 'Get Rocket Insights Recommendations', 'rocket' ),
				'description'         => __( 'Get the list of performance recommendations from Rocket Insights, including which can be actioned via WP Rocket MCP abilities.', 'rocket' ),
				'category'            => 'wp-rocket-insights',
				'input_schema'        => [
					'type' => 'null',
				],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'status'          => [
							'type'        => 'string',
							'enum'        => [ 'loading', 'completed', 'failed', 'expired' ],
							'description' => 'The current state of the recommendations data.',
						],
						'recommendations' => [
							'type'  => 'array',
							'items' => [
								'type'       => 'object',
								'properties' => [
									'option_slug'    => [ 'type' => 'string' ],
									'title'          => [ 'type' => 'string' ],
									'description'    => [ 'type' => 'string' ],
									'mcp_actionable' => [ 'type' => 'boolean' ],
									'mcp_ability'    => [ 'type' => [ 'string', 'null' ] ],
								],
							],
						],
					],
				],
				'execute_callback'    => [ $this, 'execute' ],
				'permission_callback' => [ $this, 'check_permissions' ],
				'meta'                => [
					'mcp'          => [
						'public' => true,
					],
					'show_in_rest' => true,
				],
			]
		);
	}

	/**
	 * Checks if the current user has permission to execute this ability.
	 *
	 * @return bool
	 */
	public function check_permissions(): bool {
		return current_user_can( 'rocket_manage_options' );
	}

	/**
	 * Executes the ability to get Rocket Insights recommendations.
	 *
	 * @param mixed $input Unused input parameter.
	 *
	 * @return array
	 */
	public function execute( $input = null ): array {
		$data = $this->data_manager->get_recommendations();

		if ( false === $data ) {
			return [
				'status'          => 'expired',
				'recommendations' => [],
			];
		}

		// Status whitelist guard — coerce any unrecognised or transitional status
		// to a value within the closed output_schema enum.
		// 'pending' maps to 'loading' (matches Recommendations\Render::map_status_to_state()).
		// Anything outside the known set falls back to 'failed'.
		$known_statuses = [ 'loading', 'completed', 'failed' ];
		if ( 'pending' === $data['status'] ) {
			$status = 'loading';
		} elseif ( in_array( $data['status'], $known_statuses, true ) ) {
			$status = $data['status'];
		} else {
			$status = 'failed';
		}

		$formatted = [];
		foreach ( $data['recommendations'] ?? [] as $recommendation ) {
			$slug        = $recommendation['option_slug'];
			$actionable  = in_array( $slug, self::MCP_ACTIONABLE_OPTION_SLUGS, true );
			$formatted[] = [
				'option_slug'    => $slug,
				'title'          => $recommendation['title'],
				'description'    => $recommendation['description'] ?? '',
				'mcp_actionable' => $actionable,
				'mcp_ability'    => $actionable ? 'wp-rocket/set-option' : null,
			];
		}

		return [
			'status'          => $status,
			'recommendations' => $formatted,
		];
	}
}
