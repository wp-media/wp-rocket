<?php

namespace WP_Rocket\Tests\Unit\inc\functions\admin;

use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ::rocket_create_options_hash
 *
 * @group Functions
 * @group Admin
 */
class Test_RocketCreateOptionsHash extends TestCase {
	/**
	 * The settings a site was saved with.
	 *
	 * @var array
	 */
	private $settings = [
		'cache_logged_user' => 1,
		'cache_ssl'         => 1,
		'cache_reject_ua'   => [ 'facebookexternalhit' ],
	];

	/**
	 * Keys that decide what a cached page looks like change the hash.
	 *
	 * @return void
	 */
	public function testShouldChangeWhenACachingSettingChanges() {
		$this->assertNotSame(
			rocket_create_options_hash( $this->settings ),
			rocket_create_options_hash( array_merge( $this->settings, [ 'cache_ssl' => 0 ] ) )
		);
	}

	/**
	 * Keys that do not change the hash, so that saving them does not purge the cache.
	 *
	 * @dataProvider keysThatDoNotChangeTheCache
	 *
	 * @param string $key   Option key.
	 * @param mixed  $value Value written under it.
	 *
	 * @return void
	 */
	public function testShouldNotChangeForAKeyTheCacheDoesNotDependOn( $key, $value ) {
		$this->assertSame(
			rocket_create_options_hash( $this->settings ),
			rocket_create_options_hash( array_merge( $this->settings, [ $key => $value ] ) )
		);
	}

	/**
	 * Those keys.
	 *
	 * @return array
	 */
	public function keysThatDoNotChangeTheCache() {
		return [
			// Written by a save that is not a form submission, and taken back out by the plugin's own
			// sanitize callback: a marker for one write, never a setting the cache depends on.
			'ignore'   => [ 'ignore', 1 ],
			// Which component delivers the cache, not what the cache holds.
			'maxcache' => [ 'maxcache', 1 ],
			'version'  => [ 'version', '3.24' ],
		];
	}
}
