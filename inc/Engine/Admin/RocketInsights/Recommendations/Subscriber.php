<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\Recommendations;

use WP_Rocket\Engine\Admin\RocketInsights\Context\Context;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Recommendations Subscriber.
 *
 * Handles events and hooks for the Rocket Insights Recommendations.
 *
 * @since 3.21
 */
class Subscriber implements Subscriber_Interface {
	/**
	 * Render instance.
	 *
	 * @var Render
	 */
	private $render;

	/**
	 * Context instance.
	 *
	 * @var Context
	 */
	private $context;

	/**
	 * DataManager instance.
	 *
	 * @var DataManager
	 */
	private $data_manager;

	/**
	 * Constructor.
	 *
	 * @param Render      $render  Render instance.
	 * @param Context     $context Context instance.
	 * @param DataManager $data_manager Recommendations data manager instance.
	 */
	public function __construct( Render $render, Context $context, DataManager $data_manager ) {
		$this->render       = $render;
		$this->context      = $context;
		$this->data_manager = $data_manager;
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array Array of events.
	 */
	public static function get_subscribed_events(): array {
		return [
			'rocket_sidebar'                              => 'render_recommendations_widget',
			'rocket_insights_global_score_status_changed' => 'handle_status_change',
			'rocket_insights_recommendations_rest_response' => 'output_recommendations_rest_response',
		];
	}

	/**
	 * Render the recommendations widget in the sidebar.
	 *
	 * Only renders if Rocket Insights is enabled and not on the dashboard tab.
	 * Fetching is handled asynchronously via JavaScript to avoid blocking page load.
	 *
	 * @return void
	 */
	public function render_recommendations_widget(): void {
		// Check if Rocket Insights is enabled.
		if ( ! $this->context->is_allowed() ) {
			return;
		}

		// Render from cache only - fetching is handled by JavaScript.
		$recommendations = $this->data_manager->get_recommendations();
		$this->render->render_recommendations_widget( $recommendations );
	}

	/**
	 * Output recommendations in the REST API response.
	 *
	 * Triggers fetch if recommendations are not cached yet.
	 *
	 * @param array $response_data Existing response data.
	 * @return array Modified response data with recommendations.
	 */
	public function output_recommendations_rest_response( array $response_data ): array {
		// Check if recommendations need to be fetched.
		if ( false === $this->data_manager->get_recommendations() ) {
			$this->data_manager->maybe_fetch_recommendations();
		}

		$recommendations                  = $this->data_manager->get_recommendations();
		$response_data['recommendations'] = [
			'html'     => $this->render->render_recommendations_widget( $recommendations, false ),
			'tracking' => $recommendations['tracking'] ?? null,
		];
		return $response_data;
	}

	/**
	 * Handle global score status changes.
	 *
	 * @param string $new_status New status.
	 * @return void
	 */
	public function handle_status_change( string $new_status ): void {
		switch ( $new_status ) {
			case 'in-progress':
				// Clear recommendations when tests start.
				$this->data_manager->clear_recommendations();
				break;

			case 'complete':
				// Maybe fetch recommendations when tests complete.
				$this->data_manager->maybe_fetch_recommendations();
				break;

			case 'failed':
				$this->data_manager->set_recommendations_failed( 'Global score failed' );
				break;

			default:
				// No action for other statuses.
				break;
		}
	}
}
