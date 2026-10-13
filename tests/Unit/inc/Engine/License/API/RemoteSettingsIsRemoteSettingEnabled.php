<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\API;

use Mockery;
use WP_Rocket\Engine\License\API\RemoteSettings;
use WP_Rocket\Engine\License\API\RemoteSettingsClient;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\API\RemoteSettings::is_rocket_insights_remote_setting_enabled
 *
 * @group License
 */
class RemoteSettingsIsRemoteSettingEnabled extends TestCase {
	private $api_client;
	private $remote_settings;

	public function setUp(): void {
		parent::setUp();

		$this->api_client      = Mockery::mock( RemoteSettingsClient::class );
		$this->remote_settings = new RemoteSettings( $this->api_client );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->api_client
			->shouldReceive( 'get_remote_settings_data' )
			->once()
			->andReturn( $config['remote_settings'] );

		$this->assertSame(
			$expected,
			$this->remote_settings->is_rocket_insights_remote_setting_enabled()
		);
	}
}
