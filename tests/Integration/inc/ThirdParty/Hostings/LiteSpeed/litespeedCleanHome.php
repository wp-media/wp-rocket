<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\LiteSpeed;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\LiteSpeed::litespeed_clean_domain
 *
 * @group  LiteSpeed
 * @group  ThirdParty
 */
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Filters;

class Test_LitespeedCleanHome extends TestCase {

	public function testShouldPurgeHome( ) {

		do_action( 'before_rocket_clean_home','',home_url());

		$this->assertSame(
			['X-LiteSpeed-Purge'=>'/,/page/'],
			apply_filters('wp_headers', [])
		);

	}

}
