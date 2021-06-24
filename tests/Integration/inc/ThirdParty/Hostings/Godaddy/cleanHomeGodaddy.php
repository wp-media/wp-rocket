<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Godaddy;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Godaddy::clean_home_godaddy
 *
 * @group  Godaddy
 * @group  ThirdParty
 */
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Godaddy;
use Brain\Monkey\Filters;

class Test_cleanHomeGodaddy extends TestCase {

	public function setUp(): void {
		parent::setUp();
		add_filter( 'pre_http_request', [ $this, 'mock_response' ] );
	}

	protected function tearDown(): void {
		parent::tearDown();
		remove_filter( 'pre_http_request', [ $this, 'mock_response' ]);
	}

	public function testShouldPurgeHome( ) {
		Filters\expectApplied('pre_http_request')->andReturn('response');

		do_action( 'before_rocket_clean_home', '','' );
	}

	public function mock_response() {
		return 'response';
	}

}
