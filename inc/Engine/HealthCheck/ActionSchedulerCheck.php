<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\HealthCheck;

use ActionScheduler_Versions;
use ActionScheduler;
use ActionScheduler_StoreSchema;
use ActionScheduler_LoggerSchema;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Engine\Activation\ActivationInterface;

class ActionSchedulerCheck implements Subscriber_Interface, ActivationInterface {
	/**
	 * Array of events this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		$slug = rocket_get_constant( 'WP_ROCKET_SLUG', 'wp_rocket_settings' );

		return [
			'update_option_' . $slug => [ 'check_on_update_options', 10, 2 ],
			'wp_rocket_update'       => 'maybe_recreate_as_tables',
		];
	}

	/**
	 * Actions to perform on plugin activation
	 *
	 * @return void
	 */
	public function activate() {
		// @phpstan-ignore-next-line
		add_action( 'rocket_activation', [ $this, 'maybe_recreate_as_tables' ] );
	}

	/**
	 * Maybe recreate Action Scheduler tables if they are missing
	 *
	 * @return bool
	 */
	public function maybe_recreate_as_tables(): bool {
		if ( ! $this->is_valid_action_scheduler_version() ) {
			return false;
		}

		if ( $this->is_valid_as_tables() ) {
			return false;
		}

		$store_schema  = new ActionScheduler_StoreSchema();
		$logger_schema = new ActionScheduler_LoggerSchema();
		$store_schema->register_tables( true );
		$logger_schema->register_tables( true );

		return true;
	}

	/**
	 * Maybe recreate tables on preload or RUCSS activation
	 *
	 * @param mixed $old_value The old option value.
	 * @param mixed $value The new option value.
	 *
	 * @return bool
	 */
	public function check_on_update_options( $old_value, $value ): bool {
		if ( ! isset( $old_value['remove_unused_css'], $value['remove_unused_css'], $old_value['manual_preload'], $value['manual_preload'] ) ) {
			return false;
		}

		if (
			$old_value['remove_unused_css'] === $value['remove_unused_css']
			&&
			$old_value['manual_preload'] === $value['manual_preload']
		) {
			return false;
		}

		if (
			0 === (int) $value['remove_unused_css']
			&&
			0 === (int) $value['manual_preload']
		) {
			return false;
		}

		if (
			(
				$old_value['remove_unused_css'] !== $value['remove_unused_css']
				&&
				1 !== (int) $value['remove_unused_css']
			)
			||
			(
				$old_value['manual_preload'] !== $value['manual_preload']
				&&
				1 !== (int) $value['manual_preload']
			)
		) {
			return false;
		}

		return $this->maybe_recreate_as_tables();
	}

	/**
	 * Check if Action Scheduler tables exists
	 *
	 * @return bool
	 */
	private function is_valid_as_tables(): bool {
		$cached_count = get_transient( 'rocket_rucss_as_tables_count' );

		if (
			false !== $cached_count
			&&
			! is_admin()
		) { // Stop caching in admin UI.
			return 4 === (int) $cached_count;
		}

		global $wpdb;

		$exp = "'^" . $wpdb->prefix . "actionscheduler_(logs|actions|groups|claims)$'";
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$found_as_tables = $wpdb->get_col(
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->prepare( 'SHOW TABLES FROM `' . DB_NAME . '` WHERE `Tables_in_' . DB_NAME . '` LIKE %s AND `Tables_in_' . DB_NAME . '` REGEXP ' . $exp, '%actionscheduler%' )
		);

		set_transient( 'rocket_rucss_as_tables_count', count( $found_as_tables ), rocket_get_constant( 'DAY_IN_SECONDS', 24 * 60 * 60 ) );

		return 4 === count( $found_as_tables );
	}

	/**
	 * Validate if the currenlt loaded action scheduler's version is more than 3.0.0.
	 * Note: Latest_version method in ActionScheduler_Versions class will return false with first activation
	 * in case we don't have any other active plugin which loads Action Scheduler.
	 * Because with activation, our Action Scheduler still not initialized yet.
	 *
	 * @return bool
	 */
	private function is_valid_action_scheduler_version() {
		if ( ! class_exists( 'ActionScheduler_Versions' ) || ! class_exists( 'ActionScheduler' ) ) {
			return false;
		}

		$version = ActionScheduler_Versions::instance()->latest_version();
		return ! $version || version_compare( $version, '3.0.0', '>=' );
	}
}
