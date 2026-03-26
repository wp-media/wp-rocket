<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\Abilities;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * GetInsightsScore ability instance.
	 *
	 * @var GetInsightsScore
	 */
	private $get_insights_score;

	/**
	 * Constructor.
	 *
	 * @param GetInsightsScore $get_insights_score The ability to get insights scores.
	 */
	public function __construct( GetInsightsScore $get_insights_score ) {
		$this->get_insights_score = $get_insights_score;
	}

	/**
	 * Returns an array of events this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'wp_abilities_api_init'            => 'register_get_insights_scores_ability',
			'wp_abilities_api_categories_init' => 'register_rocket_insights_category',
		];
	}

	/**
	 * Registers the ability to get insights scores.
	 */
	public function register_get_insights_scores_ability() {
		$this->get_insights_score->register_get_insights_scores_ability();
	}

	/**
	 * Registers the Rocket Insights ability category.
	 */
	public function register_rocket_insights_category() {
		wp_register_ability_category(
			'wp-rocket-insights',
			[
				'label'       => __( 'Rocket Insights', 'rocket' ),
				'description' => __( 'Abilities related to Rocket Insights performance monitoring and scoring.', 'rocket' ),
			]
		);
	}
}
