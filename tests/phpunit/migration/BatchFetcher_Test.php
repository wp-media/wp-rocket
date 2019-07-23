<?php

use Action_Scheduler\Migration\BatchFetcher;
use ActionScheduler_wpPostStore as PostStore;

/**
 * Class BatchFetcher_Test
 * @group migration
 */
class BatchFetcher_Test extends ActionScheduler_UnitTestCase {
	public function setUp() {
		parent::setUp();
		if ( ! taxonomy_exists( PostStore::GROUP_TAXONOMY ) ) {
			// register the post type and taxonomy necessary for the store to work
			$store = new PostStore();
			$store->init();
		}
	}

	public function test_nothing_to_migrate() {
		$store         = new PostStore();
		$batch_fetcher = new BatchFetcher( $store );

		$actions = $batch_fetcher->fetch();
		$this->assertEmpty( $actions );
	}

	public function test_get_due_before_future() {
		$store  = new PostStore();
		$due    = [];
		$future = [];

		for ( $i = 0; $i < 5; $i ++ ) {
			$time     = as_get_datetime_object( $i + 1 . ' minutes' );
			$schedule = new ActionScheduler_SimpleSchedule( $time );
			$action   = new ActionScheduler_Action( 'my_hook', [], $schedule );
			$future[] = $store->save_action( $action );

			$time     = as_get_datetime_object( $i + 1 . ' minutes ago' );
			$schedule = new ActionScheduler_SimpleSchedule( $time );
			$action   = new ActionScheduler_Action( 'my_hook', [], $schedule );
			$due[]    = $store->save_action( $action );
		}

		$batch_fetcher = new BatchFetcher( $store );

		$actions = $batch_fetcher->fetch();

		$this->assertEqualSets( $due, $actions );
	}


	public function test_get_future_before_complete() {
		$store    = new PostStore();
		$future   = [];
		$complete = [];

		for ( $i = 0; $i < 5; $i ++ ) {
			$time     = as_get_datetime_object( $i + 1 . ' minutes' );
			$schedule = new ActionScheduler_SimpleSchedule( $time );
			$action   = new ActionScheduler_Action( 'my_hook', [], $schedule );
			$future[] = $store->save_action( $action );

			$time       = as_get_datetime_object( $i + 1 . ' minutes ago' );
			$schedule   = new ActionScheduler_SimpleSchedule( $time );
			$action     = new ActionScheduler_FinishedAction( 'my_hook', [], $schedule );
			$complete[] = $store->save_action( $action );
		}

		$batch_fetcher = new BatchFetcher( $store );

		$actions = $batch_fetcher->fetch();

		$this->assertEqualSets( $future, $actions );
	}
}
