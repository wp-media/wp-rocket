<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\Renewal;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\License\API\{Pricing, User};
use WP_Rocket\Engine\License\Renewal;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\Renewal::get_renewal_expired_banner
 *
 * @group License
 */
class GetRenewalExpiredBanner extends TestCase {
	private $pricing;
	private $user;
	private $renewal;
	private $options;
	private $now;

	public function setUp(): void {
		parent::setUp();

		$this->pricing = Mockery::mock( Pricing::class );
		$this->user    = Mockery::mock( User::class );
		$this->options =Mockery::mock( Options_Data::class );
		$this->renewal = Mockery::mock(
			Renewal::class . '[generate,get_current_time]',
			[
				$this->pricing,
				$this->user,
				$this->options,
				'views',
			]
		)->shouldAllowMockingProtectedMethods();

		// The real clock, unless a test freezes it.
		$this->renewal->shouldReceive( 'get_current_time' )->andReturnUsing(
			function () {
				return $this->now ?? time();
			}
		);

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
	 * The gap between the 15-90 days and the more than 90 days banners: nothing is displayed.
	 */
	public function testShouldReturnEmptyStringWhenOCDEnabledAndExpiredSinceExactly90Days() {
		$this->now = 1700000000;
		$now       = $this->now;

		Functions\when( 'get_current_user_id' )->justReturn( 1 );
		Functions\when( 'get_transient' )->justReturn( false );

		$this->user->shouldReceive( 'get_license_expiration' )->andReturn( $now - 90 * DAY_IN_SECONDS );
		$this->user->shouldReceive( 'is_license_expired' )->andReturn( true );
		$this->user->shouldReceive( 'is_revoked' )->andReturn( false );
		$this->user->shouldReceive( 'is_trial_customer' )->andReturn( false );
		$this->user->shouldReceive( 'is_auto_renew' )->andReturn( false );
		$this->user->shouldReceive( 'get_renewal_url' )->andReturn( 'https://example.org/renew' );
		$this->options->shouldReceive( 'get' )->with( 'optimize_css_delivery', 0 )->andReturn( true );

		$this->renewal->shouldReceive( 'generate' )->never();

		$this->assertSame( '', $this->renewal->get_renewal_expired_banner() );
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

		$this->user->shouldReceive( 'get_license_expiration' )
			->andReturn( $config['user']['licence_expiration'] );

		$this->user->shouldReceive( 'is_auto_renew' )
			->andReturn( $config['user']['auto_renew'] );

		$this->user->shouldReceive( 'is_revoked' )
			->atMost()
			->once()
			->andReturn( $config['user']['is_revoked'] ?? false );

		$this->user->shouldReceive( 'is_trial_customer' )
			->atMost()
			->once()
			->andReturn( $config['user']['is_trial_customer'] ?? false );

		$this->options->shouldReceive( 'get' )
			->with( 'optimize_css_delivery', 0 )
			->andReturn( $config['ocd'] );

		Functions\when( 'get_current_user_id' )->justReturn( 1 );
		Functions\expect( 'get_transient' )
			->atMost()
			->once()
			->with( 'rocket_renewal_banner_1' )
			->andReturn( $config['transient'] );

		if ( ! is_null( $expected ) ) {
			$this->user->shouldReceive( 'get_license_type' )
				->atMost()
				->twice()
				->andReturn( $config['user']['licence_account'] );

			$this->user->shouldReceive( 'get_renewal_url' )
				->atMost()
				->once()
				->andReturn( $config['user']['renewal_url'] );

			$this->user->shouldReceive( 'get_creation_date' )
				->andReturn( $config['user']['creation_date'] );

			Functions\when( 'number_format_i18n' )->returnArg();

			$this->user->shouldReceive( 'get_currency' )
				->andReturn( $config['user']['currency'] ?? 'USD' );

			$this->pricing->shouldReceive( 'get_renewals_data' )
				->andReturn( $config['pricing']['renewals'] );

			$this->pricing->shouldReceive( 'get_single_websites_count' )
				->atMost()
				->twice()
				->andReturn( $config['pricing']['single']->websites );

			$this->pricing->shouldReceive( 'get_plus_websites_count' )
				->atMost()
				->twice()
				->andReturn( $config['pricing']['plus']->websites );

			$this->pricing->shouldReceive( 'get_single_pricing' )
				->atMost()
				->twice()
				->andReturn( $config['pricing']['single'] );

			$this->pricing->shouldReceive( 'get_plus_pricing' )
				->atMost()
				->once()
				->andReturn( $config['pricing']['plus'] );

			$this->pricing->shouldReceive( 'get_infinite_pricing' )
				->atMost()
				->once()
				->andReturn( $config['pricing']['infinite'] );

			$this->user->shouldReceive( 'get_license_type_slug' )
				->andReturn( '' );

			$this->pricing->shouldReceive( 'get_pricing_by_type' )
				->with( '' )
				->andReturn( null );

			Functions\when( 'date_i18n' )->justReturn( $config['disabled_date'] );

			Functions\when( 'get_option' )->justReturn( 'Ymd' );

			$this->renewal->shouldReceive( 'generate' )
				->once()
				->with(
					$expected['template'],
					$expected['data']
				)
				->andReturn( $generated );

		} else {
			$this->renewal->shouldReceive( 'generate' )
				->never();
		}

		return $this->renewal->get_renewal_expired_banner();
	}
}
