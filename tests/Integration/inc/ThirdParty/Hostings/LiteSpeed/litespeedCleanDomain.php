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

class Test_LitespeedCleanDomain extends LiteSpeedTestCase {

	public function testShouldPurgeAll( ) {
		Filters\expectApplied('wp_headers')->andReturn([]);
		do_action( 'before_rocket_clean_domain');
	}

}
