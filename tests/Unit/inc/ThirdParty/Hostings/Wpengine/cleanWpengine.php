<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Wpengine;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Wpengine::clean_wpengine
 * @group  Wpengine
 * @group  ThirdParty
 */
class Test_CleanWpengine extends WpengineTestCase {

	public function testShouldCleanWPEngine() {
		$this->wpe_common_mock->shouldReceive( 'purge_memcached' )
							->once();
		$this->wpe_common_mock->shouldReceive( 'purge_varnish_cache' )
							->once();

		$this->wpengine->clean_wpengine( );
	}
}
