<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Tracking\ChannelDetector;

use WP_Rocket\Engine\Tracking\ChannelDetector;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Tracking\ChannelDetector::detect
 * @group  Tracking
 */
class detectTest extends TestCase {
	private $detector;
	private $original_argv;

	protected function set_up(): void {
		parent::set_up();

		$this->constants['WP_CLI']     = false;
		$this->constants['DOING_AJAX'] = false;
		$this->original_argv           = $_SERVER['argv'] ?? [];
		$this->detector                = new ChannelDetector();
	}

	protected function tear_down(): void {
		$_SERVER['argv'] = $this->original_argv;
		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ): void {
		$this->constants['WP_CLI']     = $config['wp_cli'];
		$this->constants['DOING_AJAX'] = $config['doing_ajax'];
		$this->rest_request            = $config['rest_request'];
		$_SERVER['argv']               = $config['argv'];

		$this->assertSame( $expected['channel'], $this->detector->detect() );
	}
}
