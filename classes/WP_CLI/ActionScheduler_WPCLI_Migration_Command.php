<?php


namespace Action_Scheduler\WP_CLI;

use Action_Scheduler\Migration\ActionScheduler_MigrationConfig;
use Action_Scheduler\Migration\ActionScheduler_MigrationRunner;
use Action_Scheduler\Migration\ActionScheduler_MigrationScheduler;
use ActionScheduler_Data;
use WP_CLI;
use WP_CLI_Command;

/**
 * Class ActionScheduler_WPCLI_Migration_Command
 *
 * @package Action_Scheduler\WP_CLI
 *
 * @codeCoverageIgnore
 */
class ActionScheduler_WPCLI_Migration_Command extends WP_CLI_Command {

	/**
	 * Migrates actions to the DB tables store
	 */

	private $total_processed = 0;

	/**
	 * Register the command with WP-CLI
	 */
	public function register() {
		if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
			return;
		}

		WP_CLI::add_command( 'action-scheduler db-tables migrate', [ $this, 'migrate' ], [
			'shortdesc' => 'Migrates actions to the DB tables store',
			'synopsis'  => [
				[
					'type'        => 'assoc',
					'name'        => 'batch-size',
					'optional'    => true,
					'default'     => 100,
					'description' => 'The number of actions to process in each batch',
				],
				[
					'type'        => 'flag',
					'name'        => 'dry-run',
					'optional'    => true,
					'description' => 'Reports on the actions that would have been migrated, but does not change any data',
				],
			],
		] );
	}

	/**
	 * @param array $positional_args
	 * @param array $assoc_args
	 *
	 * @return void
	 */
	public function migrate( $positional_args, $assoc_args ) {
		if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
			return;
		}

		$this->init_logging();

		$config = $this->get_migration_config( $assoc_args );
		$runner = new ActionScheduler_MigrationRunner( $config );
		$runner->init_destination();

		$batch_size = isset( $assoc_args[ 'batch-size' ] ) ? (int) $assoc_args[ 'batch-size' ] : 100;

		do {
			$actions_processed     = $runner->run( $batch_size );
			$this->total_processed += $actions_processed;
		} while ( $actions_processed > 0 );

		if ( ! $config->get_dry_run() ) {
			// let the scheduler know that there's nothing left to do
			$scheduler = new ActionScheduler_MigrationScheduler();
			$scheduler->mark_complete();
		}

		WP_CLI::success( sprintf( '%s complete. %d actions processed.', $config->get_dry_run() ? 'Dry run' : 'Migration', $this->total_processed ) );
	}

	/**
	 * Build the config object used to create the ActionScheduler_MigrationRunner
	 *
	 * @param array $args
	 *
	 * @return ActionScheduler_MigrationConfig
	 */
	private function get_migration_config( $args ) {
		$args = wp_parse_args( $args, [
			'dry-run' => false,
		] );

		$config = ActionScheduler_Data::instance()->get_migration_config_object();
		$config->set_dry_run( ! empty( $args[ 'dry-run' ] ) );

		return $config;
	}

	private function init_logging() {
		add_action( 'action_scheduler/migrate_action_dry_run', function ( $action_id ) {
			WP_CLI::debug( sprintf( 'Dry-run: migrated action %d', $action_id ) );
		}, 10, 1 );
		add_action( 'action_scheduler/no_action_to_migrate', function ( $action_id ) {
			WP_CLI::debug( sprintf( 'No action found to migrate for ID %d', $action_id ) );
		}, 10, 1 );
		add_action( 'action_scheduler/migrate_action_failed', function ( $action_id ) {
			WP_CLI::warning( sprintf( 'Failed migrating action with ID %d', $action_id ) );
		}, 10, 1 );
		add_action( 'action_scheduler/migrate_action_incomplete', function ( $source_id, $destination_id ) {
			WP_CLI::warning( sprintf( 'Unable to remove source action with ID %d after migrating to new ID %d', $source_id, $destination_id ) );
		}, 10, 2 );
		add_action( 'action_scheduler/migrated_action', function ( $source_id, $destination_id ) {
			WP_CLI::debug( sprintf( 'Migrated source action with ID %d to new store with ID %d', $source_id, $destination_id ) );
		}, 10, 2 );
		add_action( 'action_scheduler/migration_batch_starting', function ( $batch ) {
			WP_CLI::debug( 'Beginning migration of batch: ' . print_r( $batch, true ) );
		}, 10, 1 );
		add_action( 'action_scheduler/migration_batch_complete', function ( $batch ) {
			WP_CLI::log( sprintf( 'Completed migration of %d actions', count( $batch ) ) );
		}, 10, 1 );
	}
}
