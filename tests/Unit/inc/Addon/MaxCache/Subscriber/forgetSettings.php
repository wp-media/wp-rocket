<?php

namespace WP_Rocket\Tests\Unit\inc\Addon\MaxCache\Subscriber;

use Mockery;
use WP_Rocket\Addon\MaxCache\MaxCache;
use WP_Rocket\Addon\MaxCache\Subscriber;
use WP_Rocket\Admin\Options;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Addon\MaxCache\Subscriber::forget_settings
 *
 * @group MaxCache
 */
class TestForgetSettings extends TestCase {
	/**
	 * Checks that what was read of the settings is dropped once the row has been written.
	 *
	 * The write is what this hangs on rather than the sanitizing before it: between the two run every
	 * other listener of that filter, and one of them reaching this add-on would have it remember the
	 * row the save is replacing — which the flush right after would then describe.
	 *
	 * @return void
	 */
	public function testShouldDropWhatWasReadOfTheSettings() {
		$forgot = false;

		$maxcache = Mockery::mock( MaxCache::class );
		$maxcache->shouldReceive( 'forget' )->once()->andReturnUsing(
			function () use ( &$forgot ) {
				$forgot = true;
			}
		);

		$subscriber = new Subscriber( $maxcache, Mockery::mock( Options::class ) );

		$subscriber->forget_settings();

		$this->assertTrue( $forgot );
	}
}
