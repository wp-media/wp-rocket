<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\AdminPageSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\CDN\RocketCDN\APIClient;
use WP_Rocket\Engine\CDN\RocketCDN\AdminPageSubscriber;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers AdminPageSubscriber::display_rocketcdn_status
 * @group  RocketCDN
 */
class Test_DisplayRocketcdnStatus extends TestCase {
	protected static $mockCommonWpFunctionsInSetUp = true;
	private $api_client;
	private $page;

	public function setUp() {
		parent::setUp();

		$this->api_client = Mockery::mock( APIClient::class );
		$this->page       = Mockery::mock(
			AdminPageSubscriber::class . '[generate]',
			[
				$this->api_client,
				Mockery::mock( Options_Data::class ),
				Mockery::mock( Beacon::class ),
				'views/settings/rocketcdn',
			]
		);
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDisplayPerData( $subscription_data, $expected, $config ) {
		Functions\when( 'rocket_is_live_site' )->justReturn( ( 'http://example.org' === $config['home_url'] ) );

		if ( ! $config['get_option'] ) {
			Functions\expect( 'get_option' )->never();
		} else {
			Functions\when( 'get_option' )->justReturn( $config['get_option'] );
		}

		if ( ! $config['date_i18n'] ) {
			Functions\expect( 'date_i18n' )->never();
		} else {
			Functions\when( 'date_i18n' )->justReturn( $config['date_i18n'] );
		}

		$this->api_client->shouldReceive( 'get_subscription_data' )
		                 ->once()
		                 ->andReturn( $subscription_data );

		$this->page->shouldReceive( 'generate' )
		           ->once()
		           ->with( 'dashboard-status', $expected['unit'] )
		           ->andReturn( '' );

		$this->expectOutputString( '' );
		$this->page->display_rocketcdn_status();
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'displayRocketcdnStatus' );
	}
}
