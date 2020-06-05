<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\AdvancedCache;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\AdvancedCache;
use WP_Rocket\Tests\Integration\CapTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers WP_Rocket\Engine\Cache\AdvancedCache::notice_content_not_ours
 * @uses   ::rocket_get_constant
 * @uses   ::rocket_notice_html
 * @uses   ::rocket_notice_writing_permissions
 *
 * @group  AdminOnly
 * @group  AdvancedCache
 */
class Test_NoticeContentNotOurs extends TestCase {
	private static $user_id;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		CapTrait::setAdminCap();
		self::$user_id = static::factory()->user->create( [ 'role' => 'administrator' ] );
	}

	public function tearDown() {
		unset( $GLOBALS['pagenow'], $_GET['activate'] );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldEchoNotice( $config, $expected ) {
		$GLOBALS['pagenow']             = $config['pagenow'];
		$_GET['activate']               = $config['activate'];
		this->wp_cache_constant         = $config['wp_cache'];
		$this->wp_rocket_advanced_cache = $config['wp_rocket_advanced_cache'];

		if ( $config['cap'] ) {
			wp_set_current_user( self::$user_id );
		}
		Functions\when( 'rocket_valid_key' )->justReturn( $config['valid_key'] );

		// Run it.
		$advanced_cache = new AdvancedCache( null, null );

		if ( empty( $expected ) ) {
			$this->assertSame( $expected, $advanced_cache->notice_content_not_ours() );

			return;
		}

		ob_start();
		$advanced_cache->notice_content_not_ours();
		$actual = ob_get_clean();
		if ( ! empty( $actual ) ) {
			$actual = $this->format_the_html( $actual );
		}
		$this->assertSame(
			$this->format_the_html( $expected ),
			$actual
		);
	}
}
