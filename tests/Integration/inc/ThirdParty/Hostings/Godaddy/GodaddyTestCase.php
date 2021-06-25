<?php
namespace WP_Rocket\Tests\integration\inc\ThirdParty\Hostings\Godaddy;

use Mockery;
use WpeCommon;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\WPEngine;

abstract class GodaddyTestCase extends TestCase {

	public function setUp(): void {
		parent::setUp();
		add_filter( 'pre_http_request', [ $this, 'mock_response' ] );
	}

	protected function tearDown(): void {
		parent::tearDown();
		remove_filter( 'pre_http_request', [ $this, 'mock_response' ]);
	}

	public function mock_response() {
		return 'response';
	}
}
