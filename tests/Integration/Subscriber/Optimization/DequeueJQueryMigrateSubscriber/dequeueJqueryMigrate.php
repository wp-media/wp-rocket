<?php
namespace WP_Rocket\Tests\Integration\Subscriber\Optimization\Dequeue_JQuery_Migrate_Subscriber;

use PHPUnit\Framework\TestCase;
use WP_Rocket\Subscriber\Optimization\Dequeue_JQuery_Migrate_Subscriber;
use Brain\Monkey;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Options;

/**
 * @covers WP_Rocket\Subscriber\Optimization\Dequeue_JQuery_Migrate_Subscriber::dequeue_jquery_migrate
 *
 * @group jQueryMigrate
 */
class Test_DequeueJqueryMigrate extends TestCase {

	public function testShouldDequeueJqueryMigrate() {
		add_filter( 'pre_get_rocket_option_dequeue_jquery_migrate', '__return_true');

		$scripts             = new \WP_Scripts();
		$jquery_dependencies = $scripts->registered['jquery']->deps;

		$this->assertFalse( in_array( 'jquery-migrate', $jquery_dependencies ) );

		remove_filter( 'pre_get_rocket_option_dequeue_jquery_migrate', '__return_true');
	}

	public function testShouldNotDequeueJqueryMigrate() {
		add_filter( 'pre_get_rocket_option_dequeue_jquery_migrate', '__return_false');

		$scripts             = new \WP_Scripts();
		$jquery_dependencies = $scripts->registered['jquery']->deps;

		$this->assertTrue( in_array( 'jquery-migrate', $jquery_dependencies ) );

		remove_filter( 'pre_get_rocket_option_dequeue_jquery_migrate', '__return_false');
	}

	public function testShouldNotDequeueJqueryMigrateWithDONOTROCKETOPTIMIZE() {
		add_filter( 'pre_get_rocket_option_dequeue_jquery_migrate', '__return_true');

		Monkey\Functions\expect( 'rocket_has_constant' )
			->with( 'DONOTROCKETOPTIMIZE' )
			->andReturn( true );
		Monkey\Functions\expect( 'rocket_get_constant' )
			->with( 'DONOTROCKETOPTIMIZE' )
			->andReturn( true );

		$scripts             = new \WP_Scripts();
		$jquery_dependencies = $scripts->registered['jquery']->deps;

		$this->assertTrue( in_array( 'jquery-migrate', $jquery_dependencies ) );

		remove_filter( 'pre_get_rocket_option_dequeue_jquery_migrate', '__return_true');
	}
}
