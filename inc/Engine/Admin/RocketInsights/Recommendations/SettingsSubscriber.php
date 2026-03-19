<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\Recommendations;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Logger\LoggerAware;
use WP_Rocket\Logger\LoggerAwareInterface;

/**
 * Recommendations Settings Subscriber.
 *
 * Detects WP Rocket settings changes and triggers recommendation updates.
 */
class SettingsSubscriber implements Subscriber_Interface, LoggerAwareInterface {
	use LoggerAware;

	/**
	 * Recommendations data manager instance.
	 *
	 * @var DataManager
	 */
	private $data_manager;

	/**
	 * Constructor.
	 *
	 * @param DataManager $data_manager Data manager instance.
	 */
	public function __construct( DataManager $data_manager ) {
		$this->data_manager = $data_manager;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'update_option_wp_rocket_settings' => [ 'maybe_fetch_after_settings_change', 10, 2 ],
		];
	}

	/**
	 * Maybe fetch recommendations after settings change.
	 *
	 * Only fetches if:
	 * - Status is completed or failed
	 * - Changed settings affect recommendations
	 *
	 * @param array $old_options Previous settings.
	 * @param array $new_options New settings.
	 * @return void
	 */
	public function maybe_fetch_after_settings_change( array $old_options, array $new_options ): void {
		// Check current status.
		$status = $this->data_manager->get_status();

		// Only proceed if recommendations exist.
		if ( ! in_array( $status, [ 'completed', 'failed' ], true ) ) {
			$this->logger::debug(
				'Recommendations: Settings changed but status not ready',
				[ 'status' => $status ]
			);
			return;
		}

		// Check if relevant settings changed.
		if ( ! $this->has_relevant_changes( $old_options, $new_options ) ) {
			$this->logger::debug( 'Recommendations: Settings changed but none affect recommendations' );
			return;
		}

		// Fetch new recommendations, we pass new options array here because at this moment options class doesn't have those new options.
		$this->fetch_recommendations( $new_options );
	}

	/**
	 * Check if changed settings affect recommendations.
	 *
	 * @param array $old_options Previous settings.
	 * @param array $new_options New settings.
	 * @return bool True if relevant changes detected.
	 */
	private function has_relevant_changes( array $old_options, array $new_options ): bool {
		// Get list of recommendation-related options from DataManager.
		$relevant_keys = DataManager::get_tracked_option_keys();

		// Check if any relevant setting changed.
		foreach ( $relevant_keys as $key ) {
			$old_value = $old_options[ $key ] ?? false;
			$new_value = $new_options[ $key ] ?? false;

			if ( $old_value !== $new_value ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Fetch new recommendations and log the action.
	 *
	 * @param array $new_options New settings to consider when fetching recommendations.
	 * @return void
	 */
	private function fetch_recommendations( array $new_options = [] ) {
		$this->logger::info( 'Recommendations: Relevant settings changed, fetching new recommendations' );

		// Fetch new recommendations - Mixpanel tracking happens automatically in DataManager.
		$this->data_manager->fetch_recommendations( $new_options );
	}
}
