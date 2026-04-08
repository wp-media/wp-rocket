<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\Abilities;

use WP_Rocket\Engine\Abilities\AbilitiesInterface;
use WP_Rocket\Engine\Admin\RocketInsights\Database\Queries\RocketInsights as Query;
use WP_Rocket\Engine\Admin\RocketInsights\GlobalScore;

class GetInsightsScore implements AbilitiesInterface {
	/**
	 * Rocket Insights Query instance.
	 *
	 * @var Query
	 */
	private $query;

	/**
	 * Global Score instance.
	 *
	 * @var GlobalScore
	 */
	private $global_score;

	/**
	 * Constructor.
	 *
	 * @param Query       $query        The query class to retrieve insights data.
	 * @param GlobalScore $global_score The class to retrieve global score data.
	 */
	public function __construct( Query $query, GlobalScore $global_score ) {
		$this->query        = $query;
		$this->global_score = $global_score;
	}

	/**
	 * Registers the ability to get insights scores.
	 */
	public function register() : void {
		wp_register_ability(
			'wp-rocket/get-insights-scores',
			[
				'label'               => __( 'Get Rocket Insights Score', 'rocket' ),
				'description'         => __( 'Gets detailed insights data for all pages monitored by Rocket Insights, and the global score.', 'rocket' ),
				'category'            => 'wp-rocket-insights',
				'input_schema'        => [
					'anyOf' => [
						[
							'type' => 'null',
						],
						[
							'type'       => 'object',
							'properties' => [
								'include_metrics' => [
									'type' => 'boolean',
								],
							],
						],
					],
				],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'summary' => [
							'type'       => 'object',
							'properties' => [
								'global_score'    => [
									'type' => 'number',
								],
								'pages_monitored' => [
									'type' => 'integer',
								],
								'status'          => [
									'type' => 'string',
								],
								'is_running'      => [
									'type' => 'boolean',
								],
							],
						],
						'results' => [
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
									'data'        => [
										'type' => 'string',
									],
									'modified'    => [
										'type'   => 'string',
										'format' => 'date-time',
									],
									'score'       => [
										'type' => [
											'number',
											'null',
										],
									],
									'report_url'  => [
										'type'   => [ 'string', 'null' ],
										'format' => 'uri',
									],
									'metric_data' => [
										'type' => [
											'string',
											'null',
										],
									],
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
	 * Executes the ability to get insights scores.
	 *
	 * @param array|null $input Optional input parameters, such as 'include_metrics' to include detailed metric data.
	 *
	 * @return array
	 */
	public function execute( $input = null ) {
		$global_score = $this->global_score->get_global_score_data();
		$results      = [];

		if ( 'no-url' === $global_score['status'] ) {
			return [
				'summary' => [
					'global_score'    => $global_score['score'],
					'pages_monitored' => $global_score['pages_num'],
					'status'          => $global_score['status'],
					'is_running'      => $global_score['is_running'],
				],
				'results' => $results,
			];
		}

		$args = [
			'fields' => [
				'url',
				'title',
				'is_mobile',
				'status',
				'data',
				'modified',
				'score',
				'report_url',
			],
		];

		if ( ! empty( $input['include_metrics'] ) ) {
			$args['fields'][] = 'metric_data';
		}

		$results = $this->query->query( $args );

		return [
			'summary' => [
				'global_score'    => $global_score['score'],
				'pages_monitored' => $global_score['pages_num'],
				'status'          => $global_score['status'],
				'is_running'      => $global_score['is_running'],
			],
			'results' => $results,
		];
	}
}
