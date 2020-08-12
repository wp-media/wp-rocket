<?php

namespace WP_Rocket\Tests\Integration\inc\classes\subscriber\Optimization\Dequeue_JQuery_Migrate_Subscriber;

use Brain\Monkey;
use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers \WP_Rocket\Subscriber\Optimization\Dequeue_JQuery_Migrate_Subscriber::dequeue_jquery_migrate
 * @group  jQueryMigrate
 */
class Test_DequeueJqueryMigrate extends TestCase {
	private $orig_deps;
	private $wp_scripts;
	private $count;

	public function setUp() {
		parent::setUp();

		$wp_scripts       = wp_scripts();
		$this->orig_deps  = $wp_scripts->registered['jquery']->deps;
		$this->wp_scripts = $wp_scripts;
		$this->count      = did_action( 'wp_default_scripts' );
	}

	public function tearDown() {
		parent::tearDown();

		// Restore.
		wp_scripts()->registered['jquery']->deps = $this->orig_deps;
	}

	public function testShouldDequeueJqueryMigrate() {
		add_filter( 'pre_get_rocket_option_dequeue_jquery_migrate', '__return_true' );

		$this->wp_scripts->init();
		$this->assertGreaterThan( $this->count, did_action( 'wp_default_scripts' ) );
		$jquery_dependencies = $this->wp_scripts->registered['jquery']->deps;

		$this->assertNotContains( 'jquery-migrate', $jquery_dependencies );

		remove_filter( 'pre_get_rocket_option_dequeue_jquery_migrate', '__return_true' );
	}

	public function testShouldNotDequeueJqueryMigrate() {
		global $wp_version;
		add_filter( 'pre_get_rocket_option_dequeue_jquery_migrate', '__return_false' );

		$this->wp_scripts->init();
		$this->assertGreaterThan( $this->count, did_action( 'wp_default_scripts' ) );
		$jquery_dependencies = $this->wp_scripts->registered['jquery']->deps;

		if ( version_compare( $wp_version, '5.5', '>=' ) ) {
			$this->assertNotContains( 'jquery-migrate', $jquery_dependencies );
		} else {
			$this->assertContains( 'jquery-migrate', $jquery_dependencies );
		}

		remove_filter( 'pre_get_rocket_option_dequeue_jquery_migrate', '__return_false' );
	}

	public function testShouldNotDequeueJqueryMigrateWithDONOTROCKETOPTIMIZE() {
		global $wp_version;

		add_filter( 'pre_get_rocket_option_dequeue_jquery_migrate', '__return_true' );

		Monkey\Functions\expect( 'rocket_get_constant' )
			->with( 'DONOTROCKETOPTIMIZE' )
			->andReturn( true );

		$this->wp_scripts->init();
		$this->assertGreaterThan( $this->count, did_action( 'wp_default_scripts' ) );
		$jquery_dependencies = $this->wp_scripts->registered['jquery']->deps;

		if ( version_compare( $wp_version, '5.5', '>=' ) ) {
			$this->assertNotContains( 'jquery-migrate', $jquery_dependencies );
		} else {
			$this->assertContains( 'jquery-migrate', $jquery_dependencies );
		}

		remove_filter( 'pre_get_rocket_option_dequeue_jquery_migrate', '__return_true' );
	}
}
