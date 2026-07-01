<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\Abilities;

use WP_Rocket\Engine\Abilities\AbilitiesInterface;
use WP_Rocket\Engine\Admin\RocketInsights\Database\Queries\RocketInsights as Query;
use WP_Rocket\Engine\Admin\RocketInsights\Managers\Plan;
use WP_Rocket\Engine\Tracking\TrackingTrait;

class GetPageInsightsScore implements AbilitiesInterface {
	use TrackingTrait;

	/**
	 * Rocket Insights Query instance.
	 *
	 * @var Query
	 */
	private $query;

	/**
	 * Plan instance.
	 *
	 * @var Plan
	 */
	private $plan;

	/**
	 * Constructor.
	 *
	 * @param Query $query The query class to retrieve insights data.
	 * @param Plan  $plan  The class responsible for retrieving plan information.
	 */
	public function __construct( Query $query, Plan $plan ) {
		$this->query = $query;
		$this->plan  = $plan;
	}

	/**
	 * Registers the ability to get page insights score.
	 */
	public function register(): void {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		wp_register_ability(
			'wp-rocket/get-page-insights-score',
			[
				'label'               => __( 'Get Rocket Insights Score for a Page', 'rocket' ),
				'description'         => _x(
					'Returns the latest Rocket Insights score for one specific URL. Requires a URI and returns score, status, report_url, and metric_data such as LCP, FCP, and TBT.
Use this when the user asks about the performance or score of one page. Do not use it for fresh tests; use retest-page-insights instead. Do not use it for all pages; use get-insights-scores instead.
If exists is false, tell the user the URL is not monitored and offer to add it with add-page-insights. This ability is read-only and does not trigger a new test.
Always open with a one-line verdict, such as `Score: 94/100 - page is healthy.` Present results using the richest available format: charts first, then a Markdown table, then structured prose.
Do not open or read GTmetrix report_url links. You may show them as complementary report links. When showing scores or metrics, use only the approved performance colors and thresholds.',
					'Ability description',
					'rocket'
					),
				'category'            => 'wp-rocket-insights',
				'input_schema'        => [
					'type'       => 'object',
					'properties' => [
						'url' => [
							'type'   => 'string',
							'format' => 'uri',
						],
					],
					'required'   => [ 'url' ],
				],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'exists'     => [
							'type' => 'boolean',
						],
						'results'    => [
							'type'  => 'array',
							'items' => [
								'type'       => 'object',
								'properties' => [
									'url'         => [
										'type'   => 'string',
										'format' => 'uri',
									],
									'title'       => [
										'type' => 'string',
									],
									'is_mobile'   => [
										'type' => 'boolean',
									],
									'status'      => [
										'type' => 'string',
									],
									'modified'    => [
										'type' => [ 'string', 'null' ],
									],
									'score'       => [
										'type' => [ 'number', 'null' ],
									],
									'report_url'  => [
										'type' => [ 'string', 'null' ],
									],
									'metric_data' => [
										'type' => [ 'object', 'null' ],
									],
								],
							],
						],
						'free_slots' => [
							'type' => 'integer',
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
	 * Checks if the current user has permission to execute this ability.
	 *
	 * @return bool
	 */
	public function check_permissions(): bool {
		return current_user_can( 'rocket_manage_options' );
	}

	/**
	 * Executes the ability to get page insights score.
	 *
	 * @param array|null $input The input data containing the URL to look up.
	 *
	 * @return array
	 */
	public function execute( $input = null ): array {
		$this->track_event( 'MCP Ability Executed', [ 'ability' => 'wp-rocket/get-page-insights-score' ] );
		$url  = rocket_add_url_protocol( $input['url'] );
		$url  = untrailingslashit( $url );
		$rows = $this->query->get_rows_by_url( $url );

		if ( false === $rows ) {
			$free_slots = max( 0, $this->plan->max_urls() - $this->query->get_total_count() );

			return [
				'exists'     => false,
				'free_slots' => $free_slots,
			];
		}

		$results = [];

		foreach ( $rows as $row ) {
			$results[] = [
				'url'         => $row->url,
				'score'       => $row->score,
				'status'      => $row->status,
				'modified'    => $row->modified ? gmdate( 'Y-m-d\TH:i:s\Z', $row->modified ) : null,
				'report_url'  => ! empty( $row->report_url ) ? $row->report_url : null,
				'metric_data' => $row->metric_data,
				'is_mobile'   => $row->is_mobile,
				'title'       => $row->title,
			];
		}

		return [
			'exists'  => true,
			'results' => $results,
		];
	}
}
