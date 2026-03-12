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
				$this->maybe_fetch_recommendations();
				break;

			default:
				// No action for other statuses.
				break;
		}
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
			$this->maybe_fetch_recommendations();
		}

		return $this->data_manager->get_recommendations();
	}

	/**
	 * Maybe fetch recommendations with validation.
	 *
	 * Checks:
	 * 1. Average metrics are available
	 * 2. Hash has changed (data is different)
	 * 3. Not already loading
	 *
	 * @return void
	 */
	private function maybe_fetch_recommendations(): void {
		// Bail if already loading.
		if ( 'loading' === $this->data_manager->get_status() ) {
			return;
		}

		// Bail if metrics not ready.
		if ( ! $this->data_manager->has_required_metrics() ) {
			return;
		}

		// Bail if data hasn't changed.
		if ( ! $this->data_manager->should_fetch_recommendations() ) {
			$this->data_manager->extend_transient(); // Extend for another 24h.
			return;
		}

		// Fetch new recommendations.
		$this->data_manager->fetch_recommendations();
	}
}
