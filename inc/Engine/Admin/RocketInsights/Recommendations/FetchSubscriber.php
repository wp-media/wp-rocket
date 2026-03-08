<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\Recommendations;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Fetch Subscriber for Recommendations.
 *
 * Listens to global score status changes and triggers recommendation
 * fetching or clearing accordingly.
 */
class FetchSubscriber implements Subscriber_Interface {
	/**
	 * DataManager instance.
	 *
	 * @var DataManager
	 */
	private $data_manager;

	/**
	 * Constructor.
	 *
	 * @param DataManager $data_manager Recommendations data manager instance.
	 */
	public function __construct( DataManager $data_manager ) {
		$this->data_manager = $data_manager;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_subscribed_events(): array {
		return [
			'rocket_insights_global_score_status_changed' => 'handle_status_change',
		];
	}

	/**
	 * Handle global score status changes.
	 *
	 * @param string $new_status      New status.
	 * @return void
	 */
	public function handle_status_change( string $new_status ): void {
		// Clear when tests start.
		if ( 'in-progress' === $new_status ) {
			$this->data_manager->clear_recommendations();
			return;
		}

		// Fetch when tests complete.
		if ( 'complete' === $new_status ) {
			$this->maybe_fetch_recommendations();
		}
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
