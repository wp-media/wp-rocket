<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\Upgrade;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\License\API\Pricing;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\License\Upgrade;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\License\Upgrade::display_promo_banner
 *
 * @group License
 */
class DisplayPromoBanner extends TestCase {
	private $pricing;
	private $user;
	private $upgrade;

	public function setUp() : void {
		parent::setUp();

		$this->pricing = Mockery::mock( Pricing::class );
		$this->user    = Mockery::mock( User::class );
		$this->upgrade = Mockery::mock(
			Upgrade::class . '[generate]',
			[
				$this->pricing,
				$this->user,
				'views',
			]
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->user->shouldReceive( 'get_license_type' )
			->atMost()
			->twice()
			->andReturn( $config['licence_account'] );

		$this->user->shouldReceive( 'is_license_expired' )
			->atMost()
			->once()
			->andReturn( $config['licence_expired'] );

		$this->user->shouldReceive( 'get_license_expiration' )
			->atMost()
			->once()
			->andReturn( $config['licence_expiration'] );

		$this->user->shouldReceive( 'get_creation_date' )
		           ->atMost()
		           ->once()
		           ->andReturn( $config['date_created'] );

		$this->pricing->shouldReceive( 'is_promo_active' )
			->atMost()
			->once()
			->andReturn( $config['promo_active'] );

		Functions\when( 'get_current_user_id' )->justReturn( 1 );
		Functions\expect( 'get_transient' )
			->atMost()
			->once()
			->with( 'rocket_promo_banner_1' )
			->andReturn( $config['transient'] );

		if ( ! is_null( $expected ) ) {
			$this->pricing->shouldReceive( 'get_promo_data' )
				->atMost()
				->once()
				->andReturn( $config['promo_data'] );

			$this->pricing->shouldReceive( 'get_single_websites_count' )
				->atMost()
				->once()
				->andReturn( $config['pricing']['single']['websites'] );

			$this->pricing->shouldReceive( 'get_plus_websites_count' )
				->atMost()
				->twice()
				->andReturn( $config['pricing']['plus']['websites'] );

			Functions\when( '_n' )
				->justReturn( $config['message'] );

			$this->pricing->shouldReceive( 'get_promo_end' )
				->atMost()
				->once()
				->andReturn( $config['promo_end'] );

			$this->upgrade->shouldReceive( 'generate' )
				->once()
				->with(
					'promo-banner',
					Mockery::on( function( $array ) use ( $expected ) {
						if( ! isset( $array['name'], $array['discount_percent'], $array['countdown'], $array['message'] ) ) {
							return false;
						}

						if( ! isset( $array['countdown']['days'], $array['countdown']['hours'], $array['countdown']['minutes'], $array['countdown']['seconds'] ) ) {
							return false;
						}

						if ( $array['name'] !== $expected['name'] ) {
							return false;
						}

						if ( $array['discount_percent'] !== $expected['discount_percent'] ) {
							return false;
						}

						if ( $array['message'] !== $expected['message'] ) {
							return false;
						}

						return true;
					} )
				)
				->andReturn( '' );

			$this->expectOutputString( '' );
		} else {
			$this->upgrade->shouldReceive( 'generate' )
				->never();
		}

		$this->upgrade->display_promo_banner();
	}
}
