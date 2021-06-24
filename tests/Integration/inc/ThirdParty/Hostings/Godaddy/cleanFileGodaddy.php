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

class Test_cleanFileGodaddy extends TestCase {

	public function setUp(): void {
		parent::setUp();
		add_filter( 'pre_http_request', [ $this, 'mock_response' ] );
	}

	protected function tearDown(): void {
		parent::tearDown();
		remove_filter( 'pre_http_request', [ $this, 'mock_response' ]);
	}

	public function testShouldPurgeFile( ) {
		Filters\expectApplied('pre_http_request')->andReturn('response');

		do_action( 'before_rocket_clean_file', home_url() );
	}

	public function mock_response() {
		return 'response';
	}
}
