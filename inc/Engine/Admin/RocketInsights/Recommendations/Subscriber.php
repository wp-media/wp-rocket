<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\Recommendations;

use WP_Rocket\Engine\Admin\RocketInsights\Context\Context;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Logger\LoggerAware;
use WP_Rocket\Logger\LoggerAwareInterface;

/**
 * Recommendations Subscriber.
 *
 * Handles events and hooks for the Rocket Insights Recommendations.
 *
 * @since 3.21
 */
class Subscriber implements Subscriber_Interface, LoggerAwareInterface {
	use LoggerAware;

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
			'wp_rocket_upgrade'                           => [ 'force_global_metrics_recalculation', 10, 2 ],
			'rocket_rocket_insights_job_deleted'          => 'maybe_clear_recommendations_on_delete',
		];
	}

	/**
	 * Render the recommendations widget in the sidebar.
	 *
	 * Only renders if Rocket Insights is enabled and not on the dashboard tab.
	 *
	 * @return void
	 */
	public function render_recommendations_widget(): void {
		// Check if Rocket Insights is enabled.
		if ( ! $this->context->is_allowed() ) {
			return;
		}

		$recommendations = $this->maybe_fetch_recommendations_on_page_load();
		$this->render->render_recommendations_widget( $recommendations );
	}

	/**
	 * Output recommendations in the REST API response.
	 *
	 * @param array $response_data Existing response data.
	 * @return array Modified response data with recommendations.
	 */
	public function output_recommendations_rest_response( array $response_data ): array {
		$recommendations                  = $this->data_manager->get_recommendations();
		$response_data['recommendations'] = [
			'html' => $this->render->render_recommendations_widget( $recommendations, false ),
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

	/**
	 * Update recommendations when a page is deleted.
	 *
	 * If no metrics remain after deletion, saves an empty state to prevent
	 * the widget from showing an infinite loading spinner.
	 *
	 * @return void
	 */
	public function maybe_clear_recommendations_on_delete(): void {
		// Trigger recommendations refresh after deletion.
		$this->data_manager->maybe_fetch_recommendations();
	}

	/**
	 * Forces global metrics recalculation when upgrading from a version older than 3.21, but not older than 3.20.
	 *
	 * @since 3.21
	 *
	 * @param string $new_version New plugin version.
	 * @param string $old_version Previously installed plugin version.
	 * @return void
	 */
	public function force_global_metrics_recalculation( string $new_version, string $old_version ): void {
		if ( version_compare( $old_version, '3.21', '>=' ) || version_compare( $old_version, '3.20', '<' ) ) {
			return;
		}

		$this->logger->info( 'Rocket Insights: Clear global score to insert average metrics when updating from a WP Rocket version less than 3.21 but not less than 3.20' );

		$this->data_manager->force_global_metrics_recalculation();
	}

	/**
	 * Fetches recommendations on page load.
	 *
	 * Checks if recommendations are available in the cache. If not, initiates fetching of recommendations.
	 * Returns the recommendations from the data manager.
	 *
	 * @return array|false
	 */
	private function maybe_fetch_recommendations_on_page_load() {
		// Bail early if no cached recommendations.
		if ( false === $this->data_manager->get_recommendations() ) {
			$this->data_manager->maybe_fetch_recommendations();
		}

		return $this->data_manager->get_recommendations();
	}
}
