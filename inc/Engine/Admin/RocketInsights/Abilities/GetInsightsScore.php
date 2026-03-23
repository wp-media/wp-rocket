<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\Abilities;

use WP_Rocket\Engine\Abilities\AbilitiesInterface;
use WP_Rocket\Engine\Admin\RocketInsights\Database\Queries\RocketInsights as Query;
use WP_Rocket\Engine\Admin\RocketInsights\GlobalScore;

class GetInsightsScore implements AbilitiesInterface {
	private $query;
	private $global_score;

	public function __construct( Query $query, GlobalScore $global_score ) {
		$this->query        = $query;
		$this->global_score = $global_score;
	}

	public function register_get_insights_score_ability() {
		wp_register_ability(
			'wp-rocket/get-insights-score',
			[
				'label' => __( 'Get Rocket Insights Score', 'rocket' ),
				'description' => __( 'Gets detailed insights data for all pages monitored by Rocket Insights, and the global score.', 'rocket' ),
				'category' => 'wp-rocket-insights',
				'output_schema' => [],
				'execute_callback' => [ $this, 'execute' ],
				'permissions_callback' => [ $this, 'check_permissions' ],
				'meta' => [
					'public' => true,
					'show_in_rest' => true,
				],
			]
		);
	}

	public function check_permissions(): bool {
		return current_user_can( 'rocket_manage_options' );
	}

	public function execute() {
		$global_score = $this->global_score->get_global_score_data();

		return [
			'global_score' => $global_score['score'],
		];
	}
}
