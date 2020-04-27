<?php


use Action_Scheduler\Migration\Config;
use Action_Scheduler\Migration\Runner;
use ActionScheduler_wpCommentLogger as CommentLogger;
use ActionScheduler_wpPostStore as PostStore;

/**
 * Class Runner_Test
 * @group migration
 */
class Runner_Test extends ActionScheduler_UnitTestCase {
	public function setUp() {
		parent::setUp();
		if ( ! taxonomy_exists( PostStore::GROUP_TAXONOMY ) ) {
			// register the post type and taxonomy necessary for the store to work
			$store = new PostStore();
			$store->init();
		}
	}

	public function test_migrate_batches() {
		$source_store       = new PostStore();
		$destination_store  = new ActionScheduler_DBStore();
		$source_logger      = new CommentLogger();
		$destination_logger = new ActionScheduler_DBLogger();

		$config = new Config();
		$config->set_source_store( $source_store );
		$config->set_source_logger( $source_logger );
		$config->set_destination_store( $destination_store );
		$config->set_destination_logger( $destination_logger );

		$runner = new Runner( $config );

		$due      = [];
		$future   = [];
		$complete = [];

		for ( $i = 0; $i < 5; $i ++ ) {
			$time     = as_get_datetime_object( $i + 1 . ' minutes' );
			$schedule = new ActionScheduler_SimpleSchedule( $time );
			$action   = new ActionScheduler_Action( 'my_hook', [], $schedule );
			$future[] = $source_store->save_action( $action );

			$time     = as_get_datetime_object( $i + 1 . ' minutes ago' );
			$schedule = new ActionScheduler_SimpleSchedule( $time );
			$action   = new ActionScheduler_Action( 'my_hook', [], $schedule );
			$due[]    = $source_store->save_action( $action );

			$time       = as_get_datetime_object( $i + 1 . ' minutes ago' );
			$schedule   = new ActionScheduler_SimpleSchedule( $time );
			$action     = new ActionScheduler_FinishedAction( 'my_hook', [], $schedule );
			$complete[] = $source_store->save_action( $action );
		}

		$created = $source_store->query_actions( [ 'per_page' => 0 ] );
		$this->assertCount( 15, $created );

		$runner->run( 10 );

		// due actions should migrate in the first batch
		$migrated = $destination_store->query_actions( [ 'per_page' => 0, 'hook' => 'my_hook' ] );
		$this->assertCount( 5, $migrated );

		$remaining = $source_store->query_actions( [ 'per_page' => 0, 'hook' => 'my_hook' ] );
		$this->assertCount( 10, $remaining );


		$runner->run( 10 );

		// pending actions should migrate in the second batch
		$migrated = $destination_store->query_actions( [ 'per_page' => 0, 'hook' => 'my_hook' ] );
		$this->assertCount( 10, $migrated );

		$remaining = $source_store->query_actions( [ 'per_page' => 0, 'hook' => 'my_hook' ] );
		$this->assertCount( 5, $remaining );


		$runner->run( 10 );

		// completed actions should migrate in the third batch
		$migrated = $destination_store->query_actions( [ 'per_page' => 0, 'hook' => 'my_hook' ] );
		$this->assertCount( 15, $migrated );

		$remaining = $source_store->query_actions( [ 'per_page' => 0, 'hook' => 'my_hook' ] );
		$this->assertCount( 0, $remaining );

	}

}