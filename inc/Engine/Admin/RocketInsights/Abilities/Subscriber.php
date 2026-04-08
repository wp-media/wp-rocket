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
	 * AddPageInsights ability instance.
	 *
	 * @var AddPageInsights
	 */
	private $add_page_insights;

	/**
	 * Constructor.
	 *
	 * @param GetInsightsScore $get_insights_score The ability to get insights scores.
	 * @param AddPageInsights  $add_page_insights  The ability to add page insights.
	 */
	public function __construct( GetInsightsScore $get_insights_score, AddPageInsights $add_page_insights ) {
		$this->get_insights_score = $get_insights_score;
		$this->add_page_insights  = $add_page_insights;
	}

	/**
	 * Returns an array of events this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'wp_abilities_api_init'            => [
				[ 'register_get_insights_scores_ability' ],
				[ 'register_add_page_insights_ability' ],
			],
			'wp_abilities_api_categories_init' => 'register_rocket_insights_category',
		];
	}

	/**
	 * Registers the ability to get insights scores.
	 */
	public function register_get_insights_scores_ability() {
		$this->get_insights_score->register();
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

	/**
	 * Registers the ability to add page insights.
	 */
	public function register_add_page_insights_ability() {
		$this->add_page_insights->register();
	}
}
