<?php

use Action_Scheduler\Migration\LogMigrator;

/**
 * Class LogMigrator_Test
 * @group migration
 */
class LogMigrator_Test extends ActionScheduler_UnitTestCase {
	function setUp() {
		parent::setUp();
		if ( ! taxonomy_exists( ActionScheduler_wpPostStore::GROUP_TAXONOMY )  ) {
			// register the post type and taxonomy necessary for the store to work
			$store = new ActionScheduler_wpPostStore();
			$store->init();
		}
	}

	public function test_migrate_from_wpComment_to_db() {
		$source = new ActionScheduler_wpCommentLogger();
		$destination = new ActionScheduler_DBLogger();
		$migrator = new LogMigrator( $source, $destination );
		$source_action_id = rand( 10, 10000 );
		$destination_action_id = rand( 10, 10000 );

		$logs = [];
		for ( $i = 0 ; $i < 3 ; $i++ ) {
			for ( $j = 0 ; $j < 5 ; $j++ ) {
				$logs[ $i ][ $j ] = md5(rand());
				if ( $i == 1 ) {
					$source->log( $source_action_id, $logs[ $i ][ $j ] );
				}
			}
		}

		$migrator->migrate( $source_action_id, $destination_action_id );

		$migrated = $destination->get_logs( $destination_action_id );
		$this->assertEqualSets( $logs[ 1 ], array_map( function( $log ) { return $log->get_message(); }, $migrated ) );

		// no API for deleting logs, so we leave them for manual cleanup later
		$this->assertCount( 5, $source->get_logs( $source_action_id ) );
	}
}