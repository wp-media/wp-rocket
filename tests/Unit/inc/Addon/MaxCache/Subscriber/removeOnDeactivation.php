<?php

namespace WP_Rocket\Tests\Unit\inc\Addon\MaxCache\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Addon\MaxCache\MaxCache;
use WP_Rocket\Addon\MaxCache\Subscriber;
use WP_Rocket\Admin\Options;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Addon\MaxCache\Subscriber::remove_on_deactivation
 *
 * @group MaxCache
 */
class TestRemoveOnDeactivation extends TestCase {
	/**
	 * Checks when the add-on takes its own section out as the plugin goes away.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   What the file holds and what the plugin's own removal did.
	 * @param array $expected Whether the section is taken out, and whether the daemon is told.
	 *
	 * @return void
	 */
	public function testShouldRemoveWhenExpected( $config, $expected ) {
		$stripped = false;
		$told     = false;

		Functions\when( 'get_home_path' )->justReturn( '/var/www/html/' );

		$maxcache = Mockery::mock( MaxCache::class );
		$maxcache->shouldReceive( 'has_directives' )->andReturn( $config['directives'] );
		$maxcache->shouldReceive( 'htaccess_path' )->andReturn( '/var/www/html/.htaccess' );
		$maxcache->shouldReceive( 'is_nginx' )->andReturn( true );
		$maxcache->shouldReceive( 'forget' );
		$maxcache->shouldReceive( 'remove_directives' )->andReturnUsing(
			function () use ( $config, &$stripped ) {
				$stripped = true;

				return $config['writable'];
			}
		);
		$maxcache->shouldReceive( 'notify_configd' )->andReturnUsing(
			function () use ( &$told ) {
				$told = true;

				return true;
			}
		);

		Functions\when( 'delete_transient' )->justReturn( true );

		$subscriber = new Subscriber( $maxcache, Mockery::mock( Options::class ) );

		$subscriber->remove_on_deactivation();

		$this->assertSame(
			$expected,
			[
				'stripped' => $stripped,
				'told'     => $told,
			]
		);
	}
}
