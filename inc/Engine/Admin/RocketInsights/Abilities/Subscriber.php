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
	 * RemovePageInsights ability instance.
	 *
	 * @var RemovePageInsights
	 */
	private $remove_page_insights;

	/**
	 * GetRecommendations ability instance.
	 *
	 * @var GetRecommendations
	 */
	private $get_recommendations;

	/**
	 * Constructor.
	 *
	 * @param GetInsightsScore   $get_insights_score   The ability to get insights scores.
	 * @param AddPageInsights    $add_page_insights    The ability to add page insights.
	 * @param RemovePageInsights $remove_page_insights The ability to remove page insights.
	 * @param GetRecommendations $get_recommendations  The ability to get recommendations.
	 */
	public function __construct( GetInsightsScore $get_insights_score, AddPageInsights $add_page_insights, RemovePageInsights $remove_page_insights, GetRecommendations $get_recommendations ) {
		$this->get_insights_score   = $get_insights_score;
		$this->add_page_insights    = $add_page_insights;
		$this->remove_page_insights = $remove_page_insights;
		$this->get_recommendations  = $get_recommendations;
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
				[ 'register_remove_page_insights_ability' ],
				[ 'register_get_recommendations_ability' ],
			],
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
		if ( ! function_exists( 'wp_register_ability_category' ) ) {
			return;
		}

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
		$this->add_page_insights->register_add_page_insights_ability();
	}

	/**
	 * Registers the ability to remove page insights.
	 */
	public function register_remove_page_insights_ability() {
		$this->remove_page_insights->register();
	}

	/**
	 * Registers the ability to get recommendations.
	 */
	public function register_get_recommendations_ability() {
		$this->get_recommendations->register();
	}
}
