<?php
namespace WP_Rocket\Tests\Unit\Subscriber\Addons\CloudflareSubscriber;

use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Subscriber\Addons\Cloudflare\CloudflareSubscriber;
use Brain\Monkey\Functions;

class TestSaveOldSettings extends TestCase {

	protected function setUp() {
		parent::setUp();

		$this->mockCommonWpFunctions();

		if ( ! defined('WEEK_IN_SECONDS') ) {
			define('WEEK_IN_SECONDS', 7 * 24 * 60 * 60);
		}
		if ( ! defined('WP_ROCKET_VERSION') ) {
			define('WP_ROCKET_VERSION', '3.5');
		}
	}

	/**
	 * Test should not save old cloudflare settings.
	 */
	public function testShouldNotSaveOldSetting() {
		$mocks = $this->getConstructorMocks( 1, '', '', '' );

		Functions\when( 'current_user_can' )->justReturn( true );

		$cloudflare = \Mockery::mock(\WP_Rocket\Addons\Cloudflare\Cloudflare::class);
		$cloudflare->shouldNotReceive('get_settings');

		$cloudflare_subscriber = new CloudflareSubscriber( $cloudflare, $mocks['options_data'], $mocks['options'] );

		$old_value = [
			'do_cloudflare'            => 1,
			'cloudflare_auto_settings' => 1,
			'cloudflare_old_settings'  => 'on,on,off,1',
		];
		$value     = [
			'do_cloudflare'            => 1,
			'cloudflare_auto_settings' => 1,
		];
		$this->assertSame(
			$value,
			$cloudflare_subscriber->save_cloudflare_old_settings( $value, $old_value )
		);
	}

	/**
	 * Test should save old cloudflare settings.
	 */
	public function testShouldSaveOldSetting() {
		$mocks = $this->getConstructorMocks( 1, '', '', '' );

		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'is_wp_error' )->justReturn( false );

		$cloudflare = \Mockery::mock(\WP_Rocket\Addons\Cloudflare\Cloudflare::class);
		$cf_settings_array = [
			'cache_level'       => 'aggressive',
			'minify'            => 'on',
			'rocket_loader'     => 'off',
			'browser_cache_ttl' => '31536000',
		];
		$cloudflare->shouldReceive('get_settings')->andReturn( $cf_settings_array );

		$cloudflare_subscriber = new CloudflareSubscriber( $cloudflare, $mocks['options_data'], $mocks['options'] );

		$old_value = [
			'do_cloudflare'            => 1,
			'cloudflare_auto_settings' => 0,
			'cloudflare_old_settings'  => 'on,on,off,1',
		];
		$value     = [
			'do_cloudflare'            => 1,
			'cloudflare_auto_settings' => 1,
		];
		$this->assertNotEquals(
			$value,
			$cloudflare_subscriber->save_cloudflare_old_settings( $value, $old_value )
		);
	}


	/**
	 * Get the mocks required by Cloudflare’s constructor.
	 *
	 * @since  3.5
	 * @author Soponar Cristina
	 * @access private
	 *
	 * @param integer $do_cloudflare      - Value to return for $options->get( 'do_cloudflare' ).
	 * @param string  $cloudflare_email   - Value to return for $options->get( 'cloudflare_email' ).
	 * @param string  $cloudflare_api_key - Value to return for $options->get( 'cloudflare_api_key' ).
	 * @param string  $cloudflare_zone_id - Value to return for $options->get( 'cloudflare_zone_id' ).
	 * @return array                      - Array of Mocks
	 */
	private function getConstructorMocks( $do_cloudflare = 1, $cloudflare_email = '',  $cloudflare_api_key = '', $cloudflare_zone_id = '') {
		$options      = $this->createMock('WP_Rocket\Admin\Options');
		$options_data = $this->createMock('WP_Rocket\Admin\Options_Data');
		$map     = [
			[
				'do_cloudflare',
				'',
				$do_cloudflare,
			],
			[
				'cloudflare_email',
				null,
				$cloudflare_email,
			],
			[
				'cloudflare_api_key',
				null,
				$cloudflare_api_key,
			],
			[
				'cloudflare_zone_id',
				null,
				$cloudflare_zone_id,
			],
		];
		$options_data->method('get')->will( $this->returnValueMap( $map ) );

		$mocks = [
			'options_data' => $options_data,
			'options'      => $options,
		];

		return $mocks;
	}
}
