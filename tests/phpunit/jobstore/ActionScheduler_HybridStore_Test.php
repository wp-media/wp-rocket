<?php


use Action_Scheduler\Migration\Config;
use ActionScheduler_NullAction as NullAction;
use ActionScheduler_wpCommentLogger as CommentLogger;
use ActionScheduler_wpPostStore as PostStore;

/**
 * Class ActionScheduler_HybridStore_Test
 * @group tables
 */

class ActionScheduler_HybridStore_Test extends ActionScheduler_UnitTestCase {
	private $demarkation_id = 1000;

	public function setUp() {
		parent::setUp();
		if ( ! taxonomy_exists( PostStore::GROUP_TAXONOMY ) ) {
			// register the post type and taxonomy necessary for the store to work
			$store = new PostStore();
			$store->init();
		}
		update_option( ActionScheduler_HybridStore::DEMARKATION_OPTION, $this->demarkation_id );
		$hybrid = new ActionScheduler_HybridStore();
		$hybrid->set_autoincrement( '', ActionScheduler_StoreSchema::ACTIONS_TABLE );
	}

	public function tearDown() {
		parent::tearDown();

		// reset the autoincrement index
		/** @var \wpdb $wpdb */
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$wpdb->actionscheduler_actions}" );
		$wpdb->query( "TRUNCATE TABLE {$wpdb->actionscheduler_logs}" );
		delete_option( ActionScheduler_HybridStore::DEMARKATION_OPTION );
	}

	public function test_actions_are_migrated_on_find() {
		$source_store       = new PostStore();
		$destination_store  = new ActionScheduler_DBStore();
		$source_logger      = new CommentLogger();
		$destination_logger = new ActionScheduler_DBLogger();

		$config = new Config();
		$config->set_source_store( $source_store );
		$config->set_source_logger( $source_logger );
		$config->set_destination_store( $destination_store );
		$config->set_destination_logger( $destination_logger );

		$hybrid_store = new ActionScheduler_HybridStore( $config );

		$time      = as_get_datetime_object( '10 minutes ago' );
		$schedule  = new ActionScheduler_SimpleSchedule( $time );
		$action    = new ActionScheduler_Action( __FUNCTION__, [], $schedule );
		$source_id = $source_store->save_action( $action );

		$found = $hybrid_store->find_action( __FUNCTION__, [] );

		$this->assertNotEquals( $source_id, $found );
		$this->assertGreaterThanOrEqual( $this->demarkation_id, $found );

		$found_in_source = $source_store->fetch_action( $source_id );
		$this->assertInstanceOf( NullAction::class, $found_in_source );
	}


	public function test_actions_are_migrated_on_query() {
		$source_store       = new PostStore();
		$destination_store  = new ActionScheduler_DBStore();
		$source_logger      = new CommentLogger();
		$destination_logger = new ActionScheduler_DBLogger();

		$config = new Config();
		$config->set_source_store( $source_store );
		$config->set_source_logger( $source_logger );
		$config->set_destination_store( $destination_store );
		$config->set_destination_logger( $destination_logger );

		$hybrid_store = new ActionScheduler_HybridStore( $config );

		$source_actions      = [];
		$destination_actions = [];

		for ( $i = 0; $i < 10; $i++ ) {
			// create in instance in the source store
			$time     = as_get_datetime_object( ( $i * 10 + 1 ) . ' minutes' );
			$schedule = new ActionScheduler_SimpleSchedule( $time );
			$action   = new ActionScheduler_Action( __FUNCTION__, [], $schedule );

			$source_actions[] = $source_store->save_action( $action );

			// create an instance in the destination store
			$time     = as_get_datetime_object( ( $i * 10 + 5 ) . ' minutes' );
			$schedule = new ActionScheduler_SimpleSchedule( $time );
			$action   = new ActionScheduler_Action( __FUNCTION__, [], $schedule );

			$destination_actions[] = $destination_store->save_action( $action );
		}

		$found = $hybrid_store->query_actions([
			'hook' => __FUNCTION__,
			'per_page' => 6,
		] );

		$this->assertCount( 6, $found );
		foreach ( $found as $key => $action_id ) {
			$this->assertNotContains( $action_id, $source_actions );
			$this->assertGreaterThanOrEqual( $this->demarkation_id, $action_id );
			if ( $key % 2 == 0 ) { // it should have been in the source store
				$this->assertNotContains( $action_id, $destination_actions );
			} else { // it should have already been in the destination store
				$this->assertContains( $action_id, $destination_actions );
			}
		}

		// six of the original 10 should have migrated to the new store
		// even though only three were retrieve in the final query
		$found_in_source = $source_store->query_actions( [
			'hook' => __FUNCTION__,
			'per_page' => 10,
		] );
		$this->assertCount( 4, $found_in_source );
	}


	public function test_actions_are_migrated_on_claim() {
		$source_store       = new PostStore();
		$destination_store  = new ActionScheduler_DBStore();
		$source_logger      = new CommentLogger();
		$destination_logger = new ActionScheduler_DBLogger();

		$config = new Config();
		$config->set_source_store( $source_store );
		$config->set_source_logger( $source_logger );
		$config->set_destination_store( $destination_store );
		$config->set_destination_logger( $destination_logger );

		$hybrid_store = new ActionScheduler_HybridStore( $config );

		$source_actions      = [];
		$destination_actions = [];

		for ( $i = 0; $i < 10; $i++ ) {
			// create in instance in the source store
			$time     = as_get_datetime_object( ( $i * 10 + 1 ) . ' minutes ago' );
			$schedule = new ActionScheduler_SimpleSchedule( $time );
			$action   = new ActionScheduler_Action( __FUNCTION__, [], $schedule );

			$source_actions[] = $source_store->save_action( $action );

			// create an instance in the destination store
			$time     = as_get_datetime_object( ( $i * 10 + 5 ) . ' minutes ago' );
			$schedule = new ActionScheduler_SimpleSchedule( $time );
			$action   = new ActionScheduler_Action( __FUNCTION__, [], $schedule );

			$destination_actions[] = $destination_store->save_action( $action );
		}

		$claim = $hybrid_store->stake_claim( 6 );

		$claimed_actions = $claim->get_actions();
		$this->assertCount( 6, $claimed_actions );
		$this->assertCount( 3, array_intersect( $destination_actions, $claimed_actions ) );


		// six of the original 10 should have migrated to the new store
		// even though only three were retrieve in the final claim
		$found_in_source = $source_store->query_actions( [
			'hook' => __FUNCTION__,
			'per_page' => 10,
		] );
		$this->assertCount( 4, $found_in_source );

		$this->assertEquals( 0, $source_store->get_claim_count() );
		$this->assertEquals( 1, $destination_store->get_claim_count() );
		$this->assertEquals( 1, $hybrid_store->get_claim_count() );

	}

	public function test_fetch_respects_demarkation() {
		$source_store       = new PostStore();
		$destination_store  = new ActionScheduler_DBStore();
		$source_logger      = new CommentLogger();
		$destination_logger = new ActionScheduler_DBLogger();

		$config = new Config();
		$config->set_source_store( $source_store );
		$config->set_source_logger( $source_logger );
		$config->set_destination_store( $destination_store );
		$config->set_destination_logger( $destination_logger );

		$hybrid_store = new ActionScheduler_HybridStore( $config );

		$source_actions      = [];
		$destination_actions = [];

		for ( $i = 0; $i < 2; $i++ ) {
			// create in instance in the source store
			$time     = as_get_datetime_object( ( $i * 10 + 1 ) . ' minutes ago' );
			$schedule = new ActionScheduler_SimpleSchedule( $time );
			$action   = new ActionScheduler_Action( __FUNCTION__, [], $schedule );

			$source_actions[] = $source_store->save_action( $action );

			// create an instance in the destination store
			$time     = as_get_datetime_object( ( $i * 10 + 5 ) . ' minutes ago' );
			$schedule = new ActionScheduler_SimpleSchedule( $time );
			$action   = new ActionScheduler_Action( __FUNCTION__, [], $schedule );

			$destination_actions[] = $destination_store->save_action( $action );
		}

		foreach ( $source_actions as $action_id ) {
			$action = $hybrid_store->fetch_action( $action_id );
			$this->assertInstanceOf( ActionScheduler_Action::class, $action );
			$this->assertNotInstanceOf( NullAction::class, $action );
		}

		foreach ( $destination_actions as $action_id ) {
			$action = $hybrid_store->fetch_action( $action_id );
			$this->assertInstanceOf( ActionScheduler_Action::class, $action );
			$this->assertNotInstanceOf( NullAction::class, $action );
		}
	}

	public function test_mark_complete_respects_demarkation() {
		$source_store       = new PostStore();
		$destination_store  = new ActionScheduler_DBStore();
		$source_logger      = new CommentLogger();
		$destination_logger = new ActionScheduler_DBLogger();

		$config = new Config();
		$config->set_source_store( $source_store );
		$config->set_source_logger( $source_logger );
		$config->set_destination_store( $destination_store );
		$config->set_destination_logger( $destination_logger );

		$hybrid_store = new ActionScheduler_HybridStore( $config );

		$source_actions      = [];
		$destination_actions = [];

		for ( $i = 0; $i < 2; $i++ ) {
			// create in instance in the source store
			$time     = as_get_datetime_object( ( $i * 10 + 1 ) . ' minutes ago' );
			$schedule = new ActionScheduler_SimpleSchedule( $time );
			$action   = new ActionScheduler_Action( __FUNCTION__, [], $schedule );

			$source_actions[] = $source_store->save_action( $action );

			// create an instance in the destination store
			$time     = as_get_datetime_object( ( $i * 10 + 5 ) . ' minutes ago' );
			$schedule = new ActionScheduler_SimpleSchedule( $time );
			$action   = new ActionScheduler_Action( __FUNCTION__, [], $schedule );

			$destination_actions[] = $destination_store->save_action( $action );
		}

		foreach ( $source_actions as $action_id ) {
			$hybrid_store->mark_complete( $action_id );
			$action = $hybrid_store->fetch_action( $action_id );
			$this->assertInstanceOf( ActionScheduler_FinishedAction::class, $action );
		}

		foreach ( $destination_actions as $action_id ) {
			$hybrid_store->mark_complete( $action_id );
			$action = $hybrid_store->fetch_action( $action_id );
			$this->assertInstanceOf( ActionScheduler_FinishedAction::class, $action );
		}
	}
}