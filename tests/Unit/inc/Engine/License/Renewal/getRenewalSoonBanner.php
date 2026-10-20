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
 * Test class covering \WP_Rocket\Engine\License\Renewal::get_renewal_soon_banner
 *
 * @group License
 */
class GetRenewalSoonBanner extends TestCase {
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

		if(! defined('WP_ROCKET_PLUGIN_NAME')) {
			define('WP_ROCKET_PLUGIN_NAME', 'WP Rocket');
		}

		$this->stubEscapeFunctions();
		$this->stubTranslationFunctions();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->assertSame(
			is_null( $expected ) ? '' : 'banner html',
			$this->getBanner( $config, $expected, 'banner html' )
		);
	}

	/**
	 * @dataProvider configNonEmptyData
	 */
	public function testShouldReturnEmptyStringWhenGenerateReturnsNull( $config, $expected ) {
		$this->assertSame( '', $this->getBanner( $config, $expected, null ) );
	}

	public function configNonEmptyData() {
		return array_filter(
			$this->configTestData(),
			function ( $case ) {
				return ! is_null( $case['expected'] );
			}
		);
	}

	private function getBanner( $config, $expected, $generated ) {
		$this->user->shouldReceive( 'is_license_expired' )
			->atMost()
			->once()
			->andReturn( $config['user']['licence_expired'] );

		$this->user->shouldReceive( 'is_auto_renew' )
			->atMost()
			->once()
			->andReturn( $config['user']['auto_renew'] );

		$this->user->shouldReceive( 'is_revoked' )
			->atMost()
			->once()
			->andReturn( $config['user']['is_revoked'] ?? false );

		$this->user->shouldReceive( 'is_trial_customer' )
			->atMost()
			->once()
			->andReturn( $config['user']['is_trial_customer'] ?? false );

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
				->andReturn( $generated );

			$this->user->shouldReceive( 'get_currency' )
				->andReturn( $config['user']['currency'] ?? 'USD' );

		} else {
			$this->renewal->shouldReceive( 'generate' )
				->never();
		}

		return $this->renewal->get_renewal_soon_banner();
	}
}
