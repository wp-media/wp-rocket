<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Godaddy;

use WP_Rocket\Tests\Integration\TestCase;

abstract class GodaddyTestCase extends TestCase {

	public function set_up() {
		parent::set_up();
		add_filter( 'pre_http_request', [ $this, 'mock_response' ] );
	}

	public function tear_down() {
		parent::tear_down();
		remove_filter( 'pre_http_request', [ $this, 'mock_response' ]);
	}

	public function mock_response() {
		return 'response';
	}
}
