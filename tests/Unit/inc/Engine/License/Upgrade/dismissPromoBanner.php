<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\Upgrade;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\License\API\Pricing;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\License\Upgrade;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\Upgrade::dismiss_promo_banner
 *
 * @group License
 */
class DismissPromoBanner extends TestCase {
	private $pricing;
	private $user;
	private $upgrade;

	public function setUp() : void {
		parent::setUp();

		$this->pricing = Mockery::mock( Pricing::class );
		$this->user    = Mockery::mock( User::class );
		$this->upgrade =  new Upgrade(
			$this->pricing,
			$this->user,
			'views'
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		Functions\when( 'check_ajax_referer' )->justReturn( true );

		Functions\when( 'current_user_can' )->justReturn( $config['cap'] );

		Functions\when( 'get_current_user_id' )->justReturn( 1 );
		Functions\expect( 'get_transient' )
			->atMost()
			->once()
			->with( 'rocket_promo_banner_1' )
			->andReturn( $config['transient'] );
	
		if ( $expected ) {
			Functions\expect( 'set_transient' )
				->once()
				->with( 'rocket_promo_banner_1', 1, 2 * WEEK_IN_SECONDS );
			
			Functions\expect( 'wp_send_json_success' )->once();
		} else {
			Functions\expect( 'set_transient' )->never();
			Functions\expect( 'wp_send_json_success' )->never();
		}

		$this->upgrade->dismiss_promo_banner();
	}
}
