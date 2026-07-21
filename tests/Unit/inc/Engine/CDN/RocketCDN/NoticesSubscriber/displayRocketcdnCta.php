<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\NoticesSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\CDN\RocketCDN\APIClient;
use WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Engine\Tracking\Tracking;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber::display_rocketcdn_cta
 *
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber::display_rocketcdn_cta
 * @group  RocketCDN
 * @group  RocketCDNNotices
 */
class Test_DisplayRocketcdnCta extends TestCase {

	/**
	 * @var Mockery\MockInterface|APIClient
	 */
	private $api_client;

	/**
	 * @var Mockery\MockInterface|Beacon
	 */
	private $beacon;

	/**
	 * @var Mockery\MockInterface|UserClient
	 */
	private $user_client;

	/**
	 * @var Mockery\MockInterface|Tracking
	 */
	private $tracking;

	/**
	 * @var Mockery\MockInterface|Options_Data
	 */
	private $options;

	/**
	 * @var Mockery\MockInterface|SubscriptionController
	 */
	private $subscription_controller;

	/**
	 * @var Mockery\MockInterface|User
	 */
	private $user;

	/**
	 * @var Mockery\MockInterface|NoticesSubscriber
	 */
	private $subscriber;

	public function set_up(): void {
		parent::set_up();

		Functions\stubTranslationFunctions();

		// Provide a minimal $wp_locale so price-formatting code doesn't fatal.
		global $wp_locale;
		$wp_locale                        = new \stdClass();
		$wp_locale->number_format         = [ 'decimal_point' => '.', 'thousands_sep' => ',' ];

		$this->api_client              = Mockery::mock( APIClient::class );
		$this->beacon                  = Mockery::mock( Beacon::class );
		$this->user_client             = Mockery::mock( UserClient::class );
		$this->tracking                = Mockery::mock( Tracking::class );
		$this->options                 = Mockery::mock( Options_Data::class );
		$this->subscription_controller = Mockery::mock( SubscriptionController::class );
		$this->user                    = Mockery::mock( User::class );

		$this->subscriber = Mockery::mock(
			NoticesSubscriber::class . '[generate]',
			[
				$this->api_client,
				$this->beacon,
				$this->user_client,
				$this->tracking,
				'',
				$this->options,
				$this->subscription_controller,
				$this->user,
			]
		);
	}

	/**
	 * @dataProvider configTestData
	 *
	 * @param array      $config   Test configuration.
	 * @param array|bool $expected Array of key=>value pairs to assert in the data passed to
	 *                             generate(), or false when generate() must not be called.
	 */
	public function testShouldHandleDisplayConditions( array $config, $expected ): void {
		$is_live_site            = $config['is_live_site'] ?? true;
		$has_active_subscription = $config['has_active_subscription'] ?? false;
		$is_paid                 = $config['is_paid'] ?? false;
		$cta_hidden              = $config['cta_hidden'] ?? false;
		$pricing_is_error        = $config['pricing_is_error'] ?? false;

		Functions\expect( 'apply_filters' )
			->with( 'rocket_display_rocketcdn_cta', true )
			->andReturn( $config['filter_result'] ?? true );

		$this->user->shouldReceive( 'is_reseller_account' )
			->andReturn( $config['is_reseller'] ?? false );

		Functions\expect( 'rocket_is_live_site' )
			->andReturn( $is_live_site );

		if ( ! $is_live_site ) {
			$this->subscriber->shouldNotReceive( 'generate' );
			$this->subscriber->display_rocketcdn_cta( $this->buildCtaData() );
			return;
		}

		// get_subscription_data is always called after the live-site check (result unused).
		$this->api_client->shouldReceive( 'get_subscription_data' )
			->andReturn( [
				'subscription_status' => $config['subscription_status'] ?? 'cancelled',
				'plan_type'           => $config['plan_type'] ?? 'free',
			] );

		$this->subscription_controller->shouldReceive( 'has_active_subscription' )
			->andReturn( $has_active_subscription );
		$this->subscription_controller->shouldReceive( 'is_paid' )
			->andReturn( $is_paid );

		if ( $has_active_subscription && $is_paid ) {
			$this->subscriber->shouldNotReceive( 'generate' );
			$this->subscriber->display_rocketcdn_cta( $this->buildCtaData() );
			return;
		}

		// Reaches the pricing / generate section.
		Functions\when( 'get_current_user_id' )->justReturn( 1 );
		Functions\when( 'get_user_meta' )->justReturn( $cta_hidden );

		// get_express_checkout_url() returns '' immediately when user data is false.
		$this->user_client->shouldReceive( 'get_user_data' )->andReturn( false );

		Functions\when( 'is_wp_error' )->justReturn( $pricing_is_error );

		if ( $pricing_is_error ) {
			$pricing_mock = Mockery::mock();
			$pricing_mock->shouldReceive( 'get_error_message' )
				->andReturn( 'RocketCDN is not available at the moment. Please retry later.' );

			$this->api_client->shouldReceive( 'get_pricing_data' )->andReturn( $pricing_mock );

			$this->beacon->shouldReceive( 'get_suggest' )
				->with( 'rocketcdn_error' )
				->andReturn( [ 'url' => '', 'id' => '' ] );

			Functions\when( 'esc_url' )->returnArg();
			Functions\when( 'esc_attr' )->returnArg();
		} else {
			$pricing = $config['pricing'];
			$this->api_client->shouldReceive( 'get_pricing_data' )->andReturn( $pricing );

			// Return the raw number so tests don't depend on locale formatting.
			Functions\when( 'number_format_i18n' )->returnArg();

			if ( $pricing['is_discount_active'] && strtotime( $pricing['end_date'] ) > time() ) {
				Functions\when( 'get_option' )->justReturn( 'Y-m-d' );
				Functions\when( 'date_i18n' )->justReturn( $pricing['end_date'] );
			}
		}

		$this->subscriber->shouldReceive( 'generate' )
			->once()
			->with(
				'cta-big',
				Mockery::on(
					static function ( array $data ) use ( $expected ) {
						foreach ( $expected as $key => $value ) {
							if ( ! array_key_exists( $key, $data ) || $data[ $key ] !== $value ) {
								return false;
							}
						}
						return true;
					}
				)
			)
			->andReturn( '' );

		ob_start();
		$this->subscriber->display_rocketcdn_cta( $this->buildCtaData() );
		ob_end_clean();
	}

	/**
	 * @return array
	 */
	private function buildCtaData(): array {
		return [
			'cta_heading'           => '',
			'cta_heading_max_limit' => '',
			'cta_description'       => '',
			'is_visible'            => true,
			'is_expanded'           => false,
			'limit_reached'         => false,
		];
	}
}
