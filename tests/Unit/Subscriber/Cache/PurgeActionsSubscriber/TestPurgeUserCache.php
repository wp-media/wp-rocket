<?php
namespace WP_Rocket\Tests\Unit\Subscriber\Cache\PurgeActionsSubscriber;

use WP_Rocket\Subscriber\Cache\PurgeActionsSubscriber;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
use Brain\Monkey\Filters;

/**
 * @coversDefaultClass \WP_Rocket\Subscriber\Cache\PurgeActionsSubscriber
 */
class TestPurgeUserCache extends TestCase {
	/**
	 * @covers::purge_user_cache
	 * @group purge_actions
	 */
	public function testShouldReturnNullWhenUserCacheDisabled() {
		$options = $this->createMock('WP_Rocket\Admin\Options_Data');
        $map     = [
            [
                'cache_logged_user',
                0,
                0,
            ],
        ];

		$options->method('get')->will($this->returnValueMap($map));

		$purge = new PurgeActionsSubscriber( $options );
		$this->assertNull($purge->purge_user_cache( 1 ));
	}

	/**
	 * @covers::purge_user_cache
	 * @group purge_actions
	 */
	public function testShouldReturnNullWhenCommonUserCache() {
		$options = $this->createMock('WP_Rocket\Admin\Options_Data');
        $map     = [
            [
                'cache_logged_user',
                0,
                1,
            ],
        ];

		$options->method('get')->will($this->returnValueMap($map));

		Filters\expectApplied('rocket_common_cache_logged_users')
		->once()
		->andReturn(true);

		$purge = new PurgeActionsSubscriber( $options );
		$this->assertNull($purge->purge_user_cache( 1 ));
	}

	/**
	 * @covers::purge_user_cache
	 * @group purge_actions
	 */
	public function testShouldPurgeCacheForUserID1() {
		$options = $this->createMock('WP_Rocket\Admin\Options_Data');
        $map     = [
            [
                'cache_logged_user',
                0,
                1,
            ],
		];

		$options->method('get')->will($this->returnValueMap($map));

		Functions\expect('rocket_clean_user')
		->once()
		->with(1);

		$purge = new PurgeActionsSubscriber( $options );
		$purge->purge_user_cache( 1 );
	}
}
