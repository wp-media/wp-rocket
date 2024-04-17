<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Godaddy;

use Brain\Monkey\Filters;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Godaddy::clean_file
 *
 * @group  Godaddy
 * @group  ThirdParty
 */
class Test_cleanFile extends GodaddyTestCase {

	public function testShouldPurgeFile( ) {
		Filters\expectApplied( 'pre_http_request' )->andReturn( 'response' );

		do_action( 'before_rocket_clean_file', home_url() );
	}

}
