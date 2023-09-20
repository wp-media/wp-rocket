<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Godaddy;

use Brain\Monkey\Filters;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Godaddy::clean_domain
 *
 * @group  Godaddy
 * @group  ThirdParty
 */
class Test_cleanDomain extends GodaddyTestCase {

	public function testShouldDoBanRequest( ) {
		Filters\expectApplied( 'pre_http_request' )->andReturn( 'response' );

		do_action( 'before_rocket_clean_domain', '', '', home_url() );
	}

}
