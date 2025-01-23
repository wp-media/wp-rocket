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

		$this->user->shouldReceive( 'get_available_upgrades' )
			->atMost()
			->twice()
			->andReturn( $config['upgrades'] ?? [] );

		if ( ! is_null( $expected ) ) {
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
