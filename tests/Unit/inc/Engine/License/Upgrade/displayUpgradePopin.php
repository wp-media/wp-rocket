<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\Upgrade;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\License\API\Pricing;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\License\Upgrade;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\Upgrade::display_upgrade_popin
 *
 * @group License
 */
class DisplayUpgradePopin extends TestCase {
	private $pricing;
	private $user;
	private $upgrade;

	public function setUp() : void {
		parent::setUp();

		Functions\stubTranslationFunctions();

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
			->andReturn( $config['license_account'] );

		$this->user->shouldReceive( 'is_license_expired' )
			->atMost()
			->once()
			->andReturn( $config['licence_expiration'] );

		if ( ! is_null( $expected ) ) {
			$this->pricing->shouldReceive( 'get_single_websites_count' )
				->atMost()
				->once()
				->andReturn( $config['pricing']['single']['websites'] );

			$this->pricing->shouldReceive( 'get_plus_websites_count' )
				->atMost()
				->twice()
				->andReturn( $config['pricing']['plus']['websites'] );

			$this->pricing->shouldReceive( 'get_single_to_plus_price' )
				->atMost()
				->once()
				->andReturn( $config['pricing']['plus']['price'] );
			
			$this->pricing->shouldReceive( 'get_regular_single_to_plus_price' )
				->atMost()
				->once()
				->andReturn( $config['pricing']['plus']['regular'] );

			$this->user->shouldReceive( 'get_upgrade_plus_url' )
				->atMost()
				->once()
				->andReturn( $config['pricing']['plus']['upgrade_url'] );

			$this->pricing->shouldReceive( 'get_single_to_infinite_price' )
				->atMost()
				->once()
				->andReturn( $config['pricing']['infinite']['price'] );
		
			$this->pricing->shouldReceive( 'get_regular_single_to_infinite_price' )
				->atMost()
				->once()
				->andReturn( $config['pricing']['infinite']['regular'] );

			$this->pricing->shouldReceive( 'get_infinite_websites_count' )
				->atMost()
				->once()
				->andReturn( $config['pricing']['infinite']['websites'] );

			$this->user->shouldReceive( 'get_upgrade_infinite_url' )
				->atMost()
				->once()
				->andReturn( $config['pricing']['infinite']['upgrade_url'] );

			$this->pricing->shouldReceive( 'get_plus_to_infinite_price' )
				->atMost()
				->once()
				->andReturn( $config['pricing']['infinite']['price'] );

			$this->pricing->shouldReceive( 'get_regular_plus_to_infinite_price' )
				->atMost()
				->once()
				->andReturn( $config['pricing']['infinite']['regular'] );

			$this->pricing->shouldReceive( 'is_promo_active' )
				->andReturn( $config['promo_active'] );

			$this->upgrade->shouldReceive( 'generate' )
				->once()
				->with(
					'upgrade-popin',
					$expected
				)
				->andReturn( '' );
			$this->expectOutputString( '' );
		} else {
			$this->upgrade->shouldReceive( 'generate' )
				->never();
		}

		$this->upgrade->display_upgrade_popin();
	}
}
