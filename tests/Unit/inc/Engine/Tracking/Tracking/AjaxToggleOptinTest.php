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
class AjaxToggleOptinTest extends TestCase {
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
		$this->mixpanel->shouldReceive( 'identify' )
			->once()
			->with( '' );

		$this->tracking = new Tracking(
			$this->options,
			$this->optin,
			$this->mixpanel,
			'path/to/templates'
		);
	}

	protected function tear_down(): void {
		unset( $_POST['value'] );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 *
	 * @return void
	 */
	public function testShouldDoExpected( $config, $expected ): void {
		Functions\when( 'check_ajax_referer' )
			->justReturn( true );

		Functions\when( 'current_user_can' )
			->justReturn( $config['capability'] );

		Functions\when( 'sanitize_key' )->alias(
			function ( $key ) {
				$key = strtolower( $key );

				return preg_replace( '/[^a-z0-9_\-]/', '', $key );
			}
		);
		Functions\when( 'wp_unslash' )->alias(
			function ( $value ) {
				return is_string( $value ) ? stripslashes( $value ) : $value;
			}
		);

		$_POST['value'] = $config['value'];

		if ( ! $expected['success'] ) {
			Functions\expect( 'wp_send_json_error' )
				->andThrow( new \Exception( $expected['message'] ) );
		} else {
			$this->optin->shouldReceive( 'enable' )
				->atMost()
				->once();

			$this->optin->shouldReceive( 'disable' )
				->atMost()
				->once();
			Functions\expect( 'wp_send_json_success' )
				->andThrow( new \Exception( $expected['message'] ) );
		}

		switch ( $expected['message'] ) {
			case 'Opt-in enabled.':
				Functions\expect( 'update_option' )
					->once()
					->with( 'rocket_analytics_notice_displayed', 1 );

				Functions\expect( 'set_transient' )
					->once()
					->with( 'rocket_analytics_optin', 1 );
				break;

			case 'Opt-in disabled.':
				Functions\expect( 'update_option' )
					->once()
					->with( 'rocket_analytics_notice_displayed', 0 );

				Functions\expect( 'set_transient' )
					->never();
				break;
		}

		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( $expected['message'] );

		$this->tracking->ajax_toggle_optin();
	}
}
