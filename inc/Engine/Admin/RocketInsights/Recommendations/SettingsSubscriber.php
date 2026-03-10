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
			'rocket_after_save_options' => [ 'maybe_fetch_after_settings_change', 10, 2 ],
		];
	}

	/**
	 * Maybe fetch recommendations after settings change.
	 *
	 * Only fetches if:
	 * - Status is completed or failed
	 * - Changed settings affect recommendations
	 * - Metrics hash would change
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

		// Check if hash would change (prevents redundant fetch).
		if ( ! $this->data_manager->should_fetch_recommendations() ) {
			$this->logger::debug( 'Recommendations: Settings changed but hash unchanged' );
			return;
		}

		$this->logger::info( 'Recommendations: Relevant settings changed, fetching new recommendations' );

		// Fetch new recommendations.
		$this->data_manager->fetch_recommendations();
	}

	/**
	 * Check if changed settings affect recommendations.
	 *
	 * @param array $old_options Previous settings.
	 * @param array $new_options New settings.
	 * @return bool True if relevant changes detected.
	 */
	private function has_relevant_changes( array $old_options, array $new_options ): bool {
		// Get list of recommendation-related options.
		$relevant_keys = [
			'minify_css',
			'minify_js',
			'minify_concatenate_css',
			'minify_concatenate_js',
			'defer_all_js',
			'delay_js',
			'async_css',
			'critical_css',
			'remove_unused_css',
			'lazyload',
			'lazyload_iframes',
			'lazyload_youtube',
			'image_dimensions',
			'cdn',
			'do_caching_mobile_files',
			'cache_logged_user',
			'cache_webp',
			'manual_preload',
			'sitemap_preload',
			'control_heartbeat',
			'minify_google_fonts',
		];

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
}
