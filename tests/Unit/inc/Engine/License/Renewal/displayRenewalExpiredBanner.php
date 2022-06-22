<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\Renewal;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\License\API\Pricing;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\License\Renewal;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\License\Renewal::display_renewal_expired_banner
 *
 * @group License
 */
class DisplayRenewalExpiredBanner extends TestCase {
	private $pricing;
	private $user;
	private $renewal;
	private $options;

	public function setUp(): void {
		parent::setUp();

		$this->pricing = Mockery::mock( Pricing::class );
		$this->user    = Mockery::mock( User::class );
		$this->options =Mockery::mock( Options_Data::class );
		$this->renewal = Mockery::mock(
			Renewal::class . '[generate]',
			[
				$this->pricing,
				$this->user,
				$this->options,
				'views',
			]
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->user->shouldReceive( 'is_license_expired' )
			->atMost()
			->once()
			->andReturn( $config['user']['licence_expired'] );

		$this->user->shouldReceive( 'get_license_expiration' )
			->andReturn( $config['user']['licence_expiration'] );

		$this->options->shouldReceive( 'get' )
			->with( 'optimize_css_delivery', 0 )
			->andReturn( 0 );

		Functions\when( 'get_current_user_id' )->justReturn( 1 );
		Functions\expect( 'get_transient' )
			->atMost()
			->once()
			->with( 'rocket_renewal_banner_1' )
			->andReturn( $config['transient'] );

		if ( ! is_null( $expected ) ) {
			$this->user->shouldReceive( 'get_license_type' )
				->atMost()
				->once()
				->andReturn( $config['user']['licence_account'] );

			$this->user->shouldReceive( 'get_renewal_url' )
				->atMost()
				->once()
				->andReturn( $config['user']['renewal_url'] );

			$this->user->shouldReceive( 'get_creation_date' )
				->andReturn( $config['user']['creation_date'] );

			$this->renewal->shouldReceive( 'generate' )
				->once()
				->with(
					'renewal-expired-banner',
					$expected
				)
				->andReturn( '' );

			$this->expectOutputString( '' );
		} else {
			$this->renewal->shouldReceive( 'generate' )
				->never();
		}

		$this->renewal->display_renewal_expired_banner();
	}
}
