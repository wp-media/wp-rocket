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

class Test_LitespeedCleanFile extends TestCase {

	public function testShouldPurgeFile( ) {

		do_action( 'before_rocket_clean_file',"/hello-world/");

		$this->assertSame(
			['X-LiteSpeed-Purge'=>'/hello-world'],
			apply_filters('wp_headers', [])
		);

	}

}
