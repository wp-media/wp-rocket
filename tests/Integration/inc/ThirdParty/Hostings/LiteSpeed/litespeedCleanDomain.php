<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\LiteSpeed;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\LiteSpeed::litespeed_clean_domain
 *
 * @group  LiteSpeed
 * @group  ThirdParty
 */
use HeaderCollector;

class Test_LitespeedCleanDomain extends LiteSpeedTestCase {

	public function testShouldPurgeAll( ) {


		do_action( 'before_rocket_clean_domain','','',home_url());
		$this->assertSame(
			['X-LiteSpeed-Purge: *'],
			HeaderCollector::$headers
		);

	}

}
