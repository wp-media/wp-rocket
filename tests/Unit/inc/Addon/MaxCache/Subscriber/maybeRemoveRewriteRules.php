<?php

namespace WP_Rocket\Tests\Unit\inc\Addon\MaxCache\Subscriber;

use Mockery;
use WP_Rocket\Addon\MaxCache\MaxCache;
use WP_Rocket\Addon\MaxCache\Subscriber;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Addon\MaxCache\Subscriber::maybe_remove_rewrite_rules
 *
 * @group MaxCache
 */
class TestMaybeRemoveRewriteRules extends TestCase {
	/**
	 * Checks what happens to the plugin's own serving rules.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array  $config   Add-on state for the case.
	 * @param string $expected Rules the plugin should end up with.
	 *
	 * @return void
	 */
	public function testShouldReturnExpectedRules( $config, $expected ) {
		$maxcache = Mockery::mock( MaxCache::class );
		$maxcache->shouldReceive( 'is_enabled' )->andReturn( $config['enabled'] );
		$maxcache->shouldReceive( 'is_nginx' )->andReturn( $config['nginx'] );

		$subscriber = new Subscriber( $maxcache, Mockery::mock( Options::class ) );

		$this->assertSame( $expected, $subscriber->maybe_remove_rewrite_rules( $config['rules'] ) );
	}
}
