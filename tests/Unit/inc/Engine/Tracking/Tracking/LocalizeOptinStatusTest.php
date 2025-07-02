<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Tracking\Tracking;

use Brain\Monkey\Functions;
use Mockery;
use WPMedia\Mixpanel\Optin;
use WPMedia\Mixpanel\TrackingPlugin as MixpanelTracking;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Tracking\Tracking;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @group Tracking
 */
class LocalizeOptinStatusTest extends TestCase {
	private $optin;
	private $mixpanel;
	private $options;
	private $tracking;

	protected function set_up(): void {
		parent::set_up();

		$this->optin    = Mockery::mock( Optin::class );
		$this->mixpanel = Mockery::mock( MixpanelTracking::class );
		$this->options  = Mockery::mock( Options_Data::class );

		$this->options->shouldReceive( 'get' )
			->with( 'consumer_email', '' )
			->andReturn( '' );

		// Mock the identify method that is called in the constructor
		$this->mixpanel->shouldReceive( 'identify' )
			->with( '' )
			->andReturnNull();

		$this->tracking = new Tracking( $this->options, $this->optin, $this->mixpanel, '' );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldLocalizeOptinStatusWhenUserHasCapability( $optin_enabled, $request_uri ) {
		Functions\when( 'current_user_can' )->justReturn( true );

		$this->optin->shouldReceive( 'is_enabled' )
			->once()
			->andReturn( $optin_enabled );

		$_SERVER['REQUEST_URI'] = $request_uri;

		$expected_data = [
			'optin_enabled' => $optin_enabled,
			'brand'         => 'WP Media',
			'product'       => 'WP Rocket',
			'context'       => 'wp_plugin',
			'path'          => $request_uri,
		];

		Functions\expect( 'wp_localize_script' )
			->once()
			->with( 'wpr-admin-common', 'rocket_mixpanel_data', $expected_data );

		Functions\when( 'esc_url_raw' )->returnArg();
		Functions\when( 'wp_unslash' )->returnArg();

		$this->tracking->localize_optin_status();
	}

	public function testShouldNotLocalizeWhenUserLacksCapability() {
		Functions\when( 'current_user_can' )->justReturn( false );

		Functions\expect( 'wp_localize_script' )->never();

		$this->tracking->localize_optin_status();
	}

	public function testShouldHandleMissingRequestUri() {
		Functions\when( 'current_user_can' )->justReturn( true );

		$this->optin->shouldReceive( 'is_enabled' )
			->once()
			->andReturn( true );

		unset( $_SERVER['REQUEST_URI'] );

		$expected_data = [
			'optin_enabled' => true,
			'brand'         => 'WP Media',
			'product'       => 'WP Rocket',
			'context'       => 'wp_plugin',
			'path'          => '',
		];

		Functions\expect( 'wp_localize_script' )
			->once()
			->with( 'wpr-admin-common', 'rocket_mixpanel_data', $expected_data );

		$this->tracking->localize_optin_status();
	}

	public function providerTestData() {
		return [
			'optin enabled with wp-admin path' => [
				'optin_enabled' => true,
				'request_uri'   => '/wp-admin/options-general.php?page=wprocket',
			],
			'optin disabled with wp-admin path' => [
				'optin_enabled' => false,
				'request_uri'   => '/wp-admin/options-general.php?page=wprocket',
			],
			'optin enabled with dashboard path' => [
				'optin_enabled' => true,
				'request_uri'   => '/wp-admin/admin.php?page=wprocket',
			],
		];
	}
}
