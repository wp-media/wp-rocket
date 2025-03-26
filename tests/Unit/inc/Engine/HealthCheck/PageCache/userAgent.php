<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\HealthCheck\PageCache;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\HealthCheck\PageCache;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\HealthCheck\PageCache::page_cache_useragent
 *
 * @group  zzz
 */
class Test_UserAgent extends TestCase {
	private $health;

	public function setUp(): void {
		parent::setUp();

		$this->health = new PageCache();

		Functions\when( 'sanitize_text_field' )->alias(
			function ( $value ) {
				return $value;
			}
		);

		Functions\when( 'wp_unslash' )->alias(
			function ( $value ) {
				return stripslashes( $value );
			}
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $request_uri, $user_agent_default, $user_agent_expected ) {
		$_SERVER['REQUEST_URI'] = $request_uri;

		$user_agent = $this->health->page_cache_useragent( $user_agent_default );
		$this->assertSame( $user_agent_expected, $user_agent );
	}
}
