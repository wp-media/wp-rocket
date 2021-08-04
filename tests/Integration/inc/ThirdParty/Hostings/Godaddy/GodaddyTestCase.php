<?php
namespace WP_Rocket\Tests\integration\inc\ThirdParty\Hostings\Godaddy;

use WP_Rocket\Tests\Integration\TestCase;

abstract class GodaddyTestCase extends TestCase {

	public function setUp(): void {
		parent::setUp();
		add_filter( 'pre_http_request', [ $this, 'mock_response' ] );
	}

	public function tearDown(): void {
		parent::tearDown();
		remove_filter( 'pre_http_request', [ $this, 'mock_response' ]);
	}

	public function mock_response() {
		return 'response';
	}
}
