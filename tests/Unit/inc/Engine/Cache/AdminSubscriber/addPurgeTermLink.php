<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\AdminSubscriber;

use Mockery;
use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Engine\Cache\AdminSubscriber;
use WP_Rocket\Engine\Cache\AdvancedCache;

/**
 * @covers WP_Rocket\Engine\Cache\AdminSubscriber::add_purge_term_link
 *
 * @group Cache
 */
class Test_AddPurgeTermLink extends TestCase {
	protected static $mockCommonWpFunctionsInSetUp = true;

	private $subscriber;

	public function setUp() {
		parent::setUp();

		$this->subscriber = new AdminSubscriber(
			Mockery::mock( AdvancedCache::class )
		);
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnActionsArray( $config, $expected ) {
		$actions = [];
		$term    = (object) [
			'term_id'  => 1,
			'taxonomy' => 'post_tag'
		];

		Functions\when( 'current_user_can' )->justReturn( $config['cap'] );
		Functions\when( 'wp_nonce_url' )->alias( function( $url ) {
			return str_replace( '&', '&amp;', "{$url}&_wpnonce=123456" );
		} );
		Functions\when( 'admin_url' )->alias( function( $path ) {
			return "http://example.org/wp-admin/{$path}";
		} );

		$actions = $this->subscriber->add_purge_term_link( $actions, $term );

		if ( $config['cap'] ) {
			$this->assertArrayHasKey( 'rocket_purge', $actions );

			$this->assertSame( $expected, $actions['rocket_purge'] );
		} else {
			$this->assertArrayNotHasKey( 'rocket_purge', $actions );
		}
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'addPurgeTermLink' );
	}
}
