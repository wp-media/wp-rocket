<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Tracking\ChannelDetector;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Tracking\ChannelDetector;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Tracking\ChannelDetector::detect
 * @group  Tracking
 */
class detectTest extends TestCase {
	private $detector;
	private $original_argv;
	private array $original_get;

	protected function set_up(): void {
		parent::set_up();

		$this->constants['WP_CLI']     = false;
		$this->constants['DOING_AJAX'] = false;
		$this->constants['WP_ADMIN']   = false;
		$this->constants['DOING_CRON'] = false;
		$this->original_argv           = $_SERVER['argv'] ?? [];
		$this->original_get            = $GLOBALS['_GET'] ?? [];
		$GLOBALS['_GET']               = [];
		$this->detector                = new ChannelDetector();
	}

	protected function tear_down(): void {
		$_SERVER['argv'] = $this->original_argv;
		$GLOBALS['_GET'] = $this->original_get;
		unset( $GLOBALS['wp'] );
		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ): void {
		$this->constants['WP_CLI']     = $config['wp_cli'];
		$this->constants['DOING_AJAX'] = $config['doing_ajax'] ?? false;
		$this->constants['WP_ADMIN']   = $config['wp_admin'] ?? false;
		$this->constants['DOING_CRON'] = $config['doing_cron'] ?? false;
		$this->rest_request            = $config['rest_request'];
		$_SERVER['argv']               = $config['argv'];

		if ( $config['rest_request'] ) {
			$rest_route = $config['rest_route'] ?? '';
			Functions\when( 'get_query_var' )->justReturn( $rest_route );
		}

		if ( isset( $config['wp_query_vars_rest_route'] ) ) {
			$wp_mock             = new \stdClass();
			$wp_mock->query_vars = [ 'rest_route' => $config['wp_query_vars_rest_route'] ];
			$GLOBALS['wp']       = $wp_mock;
		}

		Functions\when( 'wp_unslash' )->returnArg();
		Functions\when( 'sanitize_text_field' )->returnArg();

		if ( isset( $config['get_rest_route'] ) ) {
			$_GET['rest_route'] = $config['get_rest_route'];
		}

		$this->assertSame( $expected['channel'], $this->detector->detect() );
	}
}
