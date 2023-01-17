<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\HealthCheck;

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
			'rocket_disable_option_ui' => [ 'disable_rucss_preload_with_older_as_versions', 10, 2 ],
			'pre_get_rocket_option_remove_unused_css' => 'maybe_disable_options',
			'pre_get_rocket_option_manual_preload' => 'maybe_disable_options',
		];
	}

	/**
	 * Actions to perform on plugin activation
	 *
	 * @return void
	 */
	public function activate() {
		//add_filter( 'pre_get_rocket_option_manual_preload', [ $this, 'maybe_disable_options' ] );
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

	private function is_valid_action_scheduler_version() {
		if ( ! class_exists( '\ActionScheduler_Versions' ) || ! class_exists( '\ActionScheduler' ) ) {
			return false;
		}

		$version = \ActionScheduler_Versions::instance()->latest_version();
		//die(var_dump($version));
		if ( ! $version ) {
			return false;
		}

		return version_compare( $version, '3.0.0', '>=' );
	}

	public function disable_rucss_preload_with_older_as_versions( $disabled, $option_key ) {
		if ( ! in_array( $option_key, [ 'remove_unused_css', 'manual_preload' ], true ) ) {
			return $disabled;
		}

		if ( ! $this->is_valid_action_scheduler_version() ) {
			return false;
		}

		return $disabled;
	}

	public function maybe_disable_options() {
		return $this->is_valid_action_scheduler_version();
	}
}
