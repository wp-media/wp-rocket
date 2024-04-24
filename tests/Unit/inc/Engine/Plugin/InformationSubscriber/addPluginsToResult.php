<?php

namespace WP_Rocket\Tests\Unit\Inc\Plugin\InformationSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Plugin\InformationSubscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering WP_Rocket\Engine\Plugin\InformationSubscriber::add_plugins_to_result
 *
 * @group PluginUpdate
 */
class TestAddPluginsToResult extends TestCase {
	private $subscriber;

	protected function setUp(): void {
		$this->subscriber = new InformationSubscriber(
			[
				'plugin_file' => 'wp-rocket/wp-rocket.php',
				'api_url'     => 'https://wp-rocket.me',
			]
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		Functions\when( 'is_wp_error' )->justReturn( $config['wp_error'] );
		Functions\when( 'wp_list_pluck' )->justReturn( $config['list'] );
		Functions\when( 'is_plugin_active' )->justReturn( $config['plugin_active'] );
		Functions\when( 'is_plugin_active_for_network' )->justReturn( $config['plugin_active'] );
		Functions\when( 'plugins_api' )->justReturn( $config['plugins_api'] );

		$result = $this->subscriber->add_plugins_to_result( $config['result'], '', $config['args'] );

		$this->assertEquals(
			$expected,
			$result
		);
	}
}
