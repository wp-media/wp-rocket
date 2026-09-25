<?php

namespace WP_Rocket\Tests\Unit\inc\Addon\MaxCache\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Addon\MaxCache\MaxCache;
use WP_Rocket\Addon\MaxCache\Subscriber;
use WP_Rocket\Admin\Options;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Addon\MaxCache\Subscriber::after_flush
 *
 * @group MaxCache
 */
class TestAfterFlush extends TestCase {
	/**
	 * Checks what a finished write of the file leads to.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   What the write was and what the site looks like.
	 * @param array $expected Whether the daemon is told, and whether the read of the file is dropped.
	 *
	 * @return void
	 */
	public function testShouldNotifyWhenExpected( $config, $expected ) {
		$told    = false;
		$forgot  = false;

		Functions\when( 'delete_transient' )->justReturn( true );

		$maxcache = Mockery::mock( MaxCache::class );
		$maxcache->shouldReceive( 'forget' )->andReturnUsing(
			function () use ( &$forgot ) {
				$forgot = true;
			}
		);
		// Answers by the file the write has just left behind — this action fires after the write, and a
		// settings save dropped the earlier read before it. Wired so the cases can vary it: what the
		// daemon is told must not depend on whose rules the file holds once the write is done.
		$maxcache->shouldReceive( 'has_directives' )->andReturn( $config['directives'] );
		// Where the daemon is reached at all is is_nginx()'s business, and notify_configd() is what
		// asks it: what this test pins is whether the attempt is made.
		$maxcache->shouldReceive( 'decision_recorded' )->andReturn( $config['decided'] );
		$maxcache->shouldReceive( 'is_nginx' )->andReturn( true );
		$maxcache->shouldReceive( 'notify_configd' )->andReturnUsing(
			function () use ( &$told ) {
				$told = true;

				return true;
			}
		);

		$subscriber = new Subscriber( $maxcache, Mockery::mock( Options::class ) );

		$subscriber->after_flush( '/var/www/html/.htaccess', $config['changed'] );

		$this->assertSame(
			$expected,
			[
				'told'   => $told,
				'forgot' => $forgot,
			]
		);
	}
}
