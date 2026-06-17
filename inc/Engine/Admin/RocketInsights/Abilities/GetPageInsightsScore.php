<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\Abilities;

use WP_Rocket\Engine\Abilities\AbilitiesInterface;
use WP_Rocket\Engine\Admin\RocketInsights\Database\Queries\RocketInsights as Query;
use WP_Rocket\Engine\Admin\RocketInsights\Managers\Plan;

class GetPageInsightsScore implements AbilitiesInterface {
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
				'description'         => __( 'Gets detailed Rocket Insights performance results for a specific page URL.', 'rocket' ),
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
