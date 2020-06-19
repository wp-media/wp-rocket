<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Wpengine;

use Mockery;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Wpengine::clean_wpengine
 * @group  Wpengine
 * @group  ThirdParty
 */
class Test_CleanWpengine extends TestCase {

	public function testShouldCleanWPEngine() {
		$wpe_common_mock = Mockery::mock( WpeCommon::class );

		$wpe_common_mock->shouldReceive( 'purge_memcached' )
							->once();
		$wpe_common_mock->shouldReceive( 'purge_varnish_cache' )
							->once();

		do_action( 'after_rocket_clean_domain' );
	}
}
