<?php
/**
 * Created by PhpStorm.
 * User: mostafa
 * Date: 7/1/21
 * Time: 12:11 PM
 */

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\LiteSpeed;

use Mockery;
use WP_Rocket\Tests\Unit\TestCase;

abstract class LiteSpeedTestCase extends TestCase {

	public function setUp(): void {
		parent::setUp();
		add_filter( 'wp_headers', [ $this, 'mock_headers' ] );
	}

	protected function tearDown(): void {
		parent::tearDown();
		remove_filter( 'wp_headers', [ $this, 'mock_headers' ] );
	}

	public function mock_headers() {
		return [];
	}
}

