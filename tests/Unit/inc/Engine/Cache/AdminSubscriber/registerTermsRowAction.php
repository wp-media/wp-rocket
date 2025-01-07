<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\AdminSubscriber;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Fixtures\WP_Filesystem_Direct;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Engine\Cache\AdminSubscriber;
use WP_Rocket\Engine\Cache\AdvancedCache;
use WP_Rocket\Engine\Cache\WPCache;
use WP_Rocket\Event_Management\Event_Manager;
/**
 * Test class covering WP_Rocket\Engine\Cache\AdminSubscriber::register_terms_row_action
 *
 * @group Cache
 */
class Test_RegisterTermsRowAction extends TestCase {
	private $event_manager;
	private $subscriber;

	public function setUp() : void {
		parent::setUp();

		$this->event_manager = Mockery::mock( Event_manager::class );
		$this->subscriber    = new AdminSubscriber(
			Mockery::mock( AdvancedCache::class ),
			Mockery::mock( WPCache::class ),
			Mockery::mock( WP_Filesystem_Direct::class )
		);
		$this->subscriber->set_event_manager( $this->event_manager );
	}

	public function testShouldAddCallbackForEachTerm() {
		Functions\when( 'get_taxonomies' )->justReturn(
			[
				'category',
				'post_tag',
			]
		);

		$this->event_manager->shouldReceive( 'add_callback' )
			->twice();

		$this->subscriber->register_terms_row_action();
	}
}
