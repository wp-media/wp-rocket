<?php

namespace WP_Rocket\Tests\Unit\Subscriber\Addons\CloudflareSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Addons\Cloudflare\Cloudflare;
use WP_Rocket\Subscriber\Addons\Cloudflare\CloudflareSubscriber;

/**
 * @covers WP_Rocket\Addons\Cloudflare\CloudflareFacade::save_cloudflare_old_settings
 *
 * @group  Cloudflare
 */
class Test_SaveOldSettings extends CloudflareTestCase {

	protected function setUp() {
		parent::setUp();

		$this->mockCommonWpFunctions();
	}

	/**
	 * Test should not save old cloudflare settings.
	 */
	public function testShouldNotSaveOldSetting() {
		$mocks = $this->getConstructorMocks( 1, '', '', '' );

		Functions\when( 'current_user_can' )->justReturn( true );

		$cloudflare = Mockery::mock( Cloudflare::class );
		$cloudflare->shouldNotReceive( 'get_settings' );

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

		$cf_settings_array = [
			'cache_level'       => 'aggressive',
			'minify'            => 'on',
			'rocket_loader'     => 'off',
			'browser_cache_ttl' => '31536000',
		];
		$cloudflare        = Mockery::mock( Cloudflare::class, [
			'get_settings' => $cf_settings_array,
		] );

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
}
