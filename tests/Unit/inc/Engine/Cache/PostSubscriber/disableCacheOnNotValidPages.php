<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\PostSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\PostSubscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering PostSubscriber::disable_cache_on_not_valid_pages
 * @covers PostSubscriber::disable_cache_on_not_valid_pages
 *
 * @uses PostSubscriber::is_not_valid_page
 *
 * @group Cache
 */
class Test_DisableCacheOnNotValidPages extends TestCase {
	private $subscriber;

	protected function setUp(): void {
		parent::setUp();

		$this->subscriber = new PostSubscriber();
	}


	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $can_cache ) {
		Functions\expect( 'is_singular' )->once()->andReturn( ! empty( $config['is_singular'] ) );
		Functions\when( 'get_queried_object_id' )->justReturn( $config['current_post_id'] ?? 0 );

		Functions\when( 'get_permalink' )->justReturn( $config['current_post_link'] ?? '' );
		Functions\when( 'add_query_arg' )->justReturn( '' );
		Functions\when( 'home_url' )->justReturn( $config['current_page_url'] ?? '' );
		Functions\when( 'is_paged' )->justReturn( ! empty( $config['page'] ) );
		Functions\when( 'get_query_var' )->justReturn( $config['page'] ?? 0 );

		$this->assertSame( $can_cache, $this->subscriber->disable_cache_on_not_valid_pages( true ) );
	}
}
