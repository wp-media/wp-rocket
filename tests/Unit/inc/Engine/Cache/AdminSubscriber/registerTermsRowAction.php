<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\AdminSubscriber;

use Mockery;
use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Engine\Cache\AdminSubscriber;
use WP_Rocket\Event_Management\Event_Manager;

/**
 * @covers WP_Rocket\Engine\Cache\AdminSubscriber::register_terms_row_action
 *
 * @group Cache
 */
class Test_RegisterTermsRowAction extends TestCase {
	private $event_manager;
	private $subscriber;

	public function setUp() {
		parent::setUp();

		$this->event_manager = Mockery::mock( Event_manager::class );
		$this->subscriber    = new AdminSubscriber();
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
