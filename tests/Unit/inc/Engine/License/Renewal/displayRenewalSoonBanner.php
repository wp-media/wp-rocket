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
 * @covers \WP_Rocket\Engine\License\Renewal::display_renewal_soon_banner
 *
 * @group License
 */
class DisplayRenewalSoonBanner extends TestCase {
	private $pricing;
	private $user;
	private $renewal;

	public function setUp() : void {
		parent::setUp();

		$this->pricing = Mockery::mock( Pricing::class );
		$this->user    = Mockery::mock( User::class );
		$this->renewal = Mockery::mock(
			Renewal::class . '[generate]',
			[
				$this->pricing,
				$this->user,
				Mockery::mock( Options_Data::class ),
				'views',
			]
		);

		$this->stubEscapeFunctions();
		$this->stubTranslationFunctions();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->user->shouldReceive( 'is_license_expired' )
			->atMost()
			->once()
			->andReturn( $config['user']['licence_expired'] );

		$this->user->shouldReceive( 'is_auto_renew' )
			->atMost()
			->once()
			->andReturn( $config['user']['auto_renew'] );

		$this->user->shouldReceive( 'get_license_expiration' )
			->andReturn( $config['user']['licence_expiration'] );

		if ( ! is_null( $expected ) ) {
			$this->user->shouldReceive( 'get_license_type' )
				->andReturn( $config['user']['licence_account'] );

			$this->user->shouldReceive( 'get_renewal_url' )
				->atMost()
				->once()
				->andReturn( $config['user']['renewal_url'] );

			$this->user->shouldReceive( 'get_creation_date' )
				->andReturn( $config['user']['creation_date'] );

			$this->pricing->shouldReceive( 'get_renewals_data' )
				->andReturn( $config['pricing']['renewals'] );

			$this->pricing->shouldReceive( 'get_single_websites_count' )
				->andReturn( $config['pricing']['single']->websites );

			$this->pricing->shouldReceive( 'get_plus_websites_count' )
				->andReturn( $config['pricing']['plus']->websites );

			$this->pricing->shouldReceive( 'get_single_pricing' )
				->andReturn( $config['pricing']['single'] );

			$this->pricing->shouldReceive( 'get_plus_pricing' )
				->andReturn( $config['pricing']['plus'] );

			$this->pricing->shouldReceive( 'get_infinite_pricing' )
				->andReturn( $config['pricing']['infinite'] );

			Functions\when( 'number_format_i18n' )->returnArg();

			$this->renewal->shouldReceive( 'generate' )
				->once()
				->with(
					'renewal-soon-banner',
					$expected
				)
				->andReturn( '' );

			$this->expectOutputString( '' );
		} else {
			$this->renewal->shouldReceive( 'generate' )
				->never();
		}

		$this->renewal->display_renewal_soon_banner();
	}
}
