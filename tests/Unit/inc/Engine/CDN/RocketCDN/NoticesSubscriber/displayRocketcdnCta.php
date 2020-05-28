<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\NoticesSubscriber;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Engine\CDN\RocketCDN\APIClient;
use WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber::display_rocketcdn_cta
 * @uses   \WP_Rocket\Engine\CDN\RocketCDN\APIClient::get_subscription_data
 * @uses   \WP_Rocket\Engine\CDN\RocketCDN\APIClient::get_pricing_data
 *
 * @group  RocketCDN
 */
class Test_DisplayRocketcdnCta extends TestCase {
	protected static $mockCommonWpFunctionsInSetUp = true;

	private $api_client;
	private $notices;

	public function setUp() {
		parent::setUp();

		$this->api_client = Mockery::mock( APIClient::class );
		$this->notices    = Mockery::mock(
			NoticesSubscriber::class . '[generate]',
			[
				$this->api_client,
				'views/settings/rocketcdn',
			]
		);
	}

	public function tearDown() {
		$this->resetStubProperties();

		parent::tearDown();
	}

	public function testShouldDisplayNothingWhenWhiteLabel() {
		$this->white_label = true;

		$this->assertNull( $this->notices->display_rocketcdn_cta() );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDisplayPerData( $data, $expected, $config ) {
		$this->white_label = isset( $config['white_label'] ) ? $config['white_label'] : $this->white_label;

		if ( $this->white_label ) {
			$this->api_client->shouldReceive( 'get_subscription_data' )->never();
			$this->notices->shouldReceive( 'generate' )->never();
			$this->assertNull( $this->notices->display_rocketcdn_cta() );

			return;
		}

		$live_site = isset( $config['live_site'] ) ? $config['live_site'] : true;

		Functions\expect( 'rocket_is_live_site' )->once()->andReturn( $live_site );

		if ( ! $live_site ) {
			$this->api_client->shouldReceive( 'get_subscription_data' )->never();
			$this->notices->shouldReceive( 'generate' )->never();
			$this->assertNull( $this->notices->display_rocketcdn_cta() );

			return;
		}

		$this->api_client
			->shouldReceive( 'get_subscription_data' )
			->once()
			->andReturn( $data['rocketcdn_status'] );

		if ( 'running' === $data['rocketcdn_status']['subscription_status'] ) {
			$this->api_client->shouldReceive( 'rocketcdn_pricing' )->never();
			$this->notices->shouldReceive( 'generate' )->never();

			$this->assertNull( $this->notices->display_rocketcdn_cta() );

			return;
		}

		Functions\expect( 'get_current_user_id' )->once()->andReturn( 1 );
		Functions\expect( 'get_user_meta' )
			->once()
			->with( 1, 'rocket_rocketcdn_cta_hidden', true )
			->andReturn( $config['rocket_rocketcdn_cta_hidden'] );

		if ( $config['is_wp_error'] ) {
			$pricing = Mockery::mock( 'WP_Error' );
			$pricing->shouldReceive( 'get_error_message' )->once()->andReturn( $data['rocketcdn_pricing'] );

			Functions\expect( 'get_option' )->with( 'date_format' )->never();
			Functions\expect( 'date_i18n' )->withAnyArgs( 'Y-m-d' )->never();

		} else {
			$pricing = $data['rocketcdn_pricing'];

			Functions\expect( 'get_option' )->once()->with( 'date_format' )->andReturn( 'Y-m-d' );
			Functions\expect( 'date_i18n' )
				->once()
				->with( 'Y-m-d', strtotime( $data['rocketcdn_pricing']['end_date'] ) )
				->andReturn( $data['rocketcdn_pricing']['end_date'] );
		}

		$this->api_client
			->shouldReceive( 'get_pricing_data' )
			->once()
			->andReturn( $pricing );
		Functions\expect( 'is_wp_error' )
			->once()
			->with( $pricing )
			->andReturn( $config['is_wp_error'] );

		Functions\when( 'number_format_i18n' )->returnArg();

		$this->notices
			->shouldReceive( 'generate' )
			->once()
			->with( 'cta-small', $expected['unit']['cta-small'] )
			->andReturn( '' );
		$this->notices
			->shouldReceive( 'generate' )
			->once()
			->with( 'cta-big', $expected['unit']['cta-big'] )
			->andReturn( '' );

		$this->expectOutputString( '' );
		$this->notices->display_rocketcdn_cta();
	}
}
