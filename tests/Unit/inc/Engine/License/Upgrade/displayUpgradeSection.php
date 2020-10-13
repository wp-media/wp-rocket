<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\Upgrade;

use Mockery;
use WP_Rocket\Engine\License\API\Pricing;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\License\Upgrade;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\License\Upgrade::display_upgrade_section
 *
 * @group License
 */
class DisplayUpgradeSection extends TestCase {
	protected static $mockCommonWpFunctionsInSetUp = true;

	private $user;
	private $upgrade;

	public function setUp() {
		parent::setUp();

		$this->user    = Mockery::mock( User::class );
		$this->upgrade = Mockery::mock(
			Upgrade::class . '[generate]',
			[
				Mockery::mock( Pricing::class ),
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
			->once()
			->andReturn( $config['license_account'] );

		$this->user->shouldReceive( 'is_license_expired' )
			->atMost()
			->times( 1 )
			->andReturn( $config['licence_expiration'] );

		if ( ! is_null( $expected ) ) {
			$this->upgrade->shouldReceive( 'generate' )
				->once()
				->andReturn( '' );
			$this->expectOutputString( '' );
		} else {
			$this->upgrade->shouldReceive( 'generate' )
				->never();
		}

		$this->upgrade->display_upgrade_section();
	}
}
