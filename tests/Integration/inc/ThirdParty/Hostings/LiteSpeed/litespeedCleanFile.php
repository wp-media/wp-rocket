<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\LiteSpeed;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\LiteSpeed::litespeed_clean_domain
 *
 * @group  LiteSpeed
 * @group  ThirdParty
 */
use HeaderCollector;

class Test_LitespeedCleanFile extends LiteSpeedTestCase {

	public function testShouldPurgeFile( ) {

		do_action( 'before_rocket_clean_file',"http://www.example.com/hello-world/");

		$this->assertSame(
			['X-LiteSpeed-Purge: /hello-world/'],
			HeaderCollector::$headers
		);

	}

}
