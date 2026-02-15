<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\UrlValidation\PostSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\UrlValidation\PostSubscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering PostSubscriber::disable_cache_on_not_valid_url
 * @covers \WP_Rocket\Engine\Cache\UrlValidation\PostSubscriber::disable_cache_on_not_valid_url
 *
 * @uses PostSubscriber::is_not_valid_url
 *
 * @group Cache
 */
class TestDisableCacheOnNotValidUrl extends TestCase {
	private $subscriber;

	protected function setUp(): void {
		parent::setUp();

		$this->subscriber = new PostSubscriber();
	}

	protected function tearDown(): void {
		unset( $_SERVER['REQUEST_URI'] );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $can_cache ) {
		Functions\expect( 'is_singular' )->once()->andReturn( ! empty( $config['is_singular'] ) );
		Functions\when( 'get_queried_object_id' )->justReturn( $config['current_post_id'] ?? 0 );

		Functions\when( 'get_permalink' )->justReturn( $config['current_post_link'] ?? '' );
		Functions\when( 'add_query_arg' )->justReturn( '' );

		if ( isset( $config['request_uri'] ) ) {
			$_SERVER['REQUEST_URI'] = $config['request_uri'];
			Functions\when( 'wp_unslash' )->justReturn( $config['request_uri'] );
		}

		$condition = isset( $config['current_page_url'] ) && isset( $config['current_post_id']) && $config['current_post_id'] !== 0;

		switch ($condition) {
			case true:
				Functions\expect( 'home_url' )
					->times( isset( $config['request_uri'] ) ? 2 : 1 )
					->andReturn( 
						$config['current_page_url'],
						$config['second_current_page_url'] ?? ''
					);
				break;
			case false:
				Functions\expect( 'home_url' )->never();
				break;
		}

		Functions\when( 'is_paged' )->justReturn( ! empty( $config['page'] ) );
		Functions\when( 'get_query_var' )->justReturn( $config['page'] ?? 0 );

		$this->assertSame( $can_cache, $this->subscriber->disable_cache_on_not_valid_url( true ) );
	}
}
