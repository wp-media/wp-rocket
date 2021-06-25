<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Godaddy;
/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Godaddy::clean_file_godaddy
 *
 * @group  Godaddy
 * @group  ThirdParty
 */
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Filters;

class Test_cleanFileGodaddy extends GodaddyTestCase {

	public function testShouldPurgeFile( ) {
		Filters\expectApplied('pre_http_request')->andReturn('response');

		do_action( 'before_rocket_clean_file', home_url() );
	}

}
