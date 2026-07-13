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
	 * @var Mockery\MockInterface|NoticesSubscriber
	 */
	private $subscriber;

	public function set_up(): void {
		parent::set_up();

		Functions\stubTranslationFunctions();

		$this->api_client              = Mockery::mock( APIClient::class );
		$this->beacon                  = Mockery::mock( Beacon::class );
		$this->user_client             = Mockery::mock( UserClient::class );
		$this->tracking                = Mockery::mock( Tracking::class );
		$this->options                 = Mockery::mock( Options_Data::class );
		$this->subscription_controller = Mockery::mock( SubscriptionController::class );

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
			]
		);
	}

	/**
	 * Tests that display_rocketcdn_cta() returns early without rendering when the account is a reseller.
	 *
	 * The base TestCase stubs rocket_get_constant() to return false for WP_ROCKET_WHITE_LABEL_ACCOUNT,
	 * so the white-label guard is skipped and the reseller guard fires next.
	 *
	 * @return void
	 */
	public function testShouldNotGenerateWhenResellerAccount(): void {
		Functions\expect( 'apply_filters' )
			->with( 'rocket_display_rocketcdn_cta', true )
			->andReturn( true );

		$user_data              = new \stdClass();
		$user_data->is_reseller = true;

		$this->user_client->shouldReceive( 'get_user_data' )
			->once()
			->andReturn( $user_data );

		// generate() must not be called when the account is a reseller.
		$this->subscriber->shouldNotReceive( 'generate' );

		$this->subscriber->display_rocketcdn_cta( $this->buildCtaData() );
	}

	/**
	 * Tests that display_rocketcdn_cta() does not short-circuit on the reseller check
	 * when the account is not a reseller (execution continues to the live-site check).
	 *
	 * @return void
	 */
	public function testShouldProceedWhenNotResellerAccount(): void {
		Functions\expect( 'apply_filters' )
			->with( 'rocket_display_rocketcdn_cta', true )
			->andReturn( true );

		$user_data              = new \stdClass();
		$user_data->is_reseller = false;

		$this->user_client->shouldReceive( 'get_user_data' )
			->andReturn( $user_data );

		// Next guard: rocket_is_live_site() — return false so the method exits early there.
		Functions\expect( 'rocket_is_live_site' )
			->andReturn( false );

		// generate() should not be called because rocket_is_live_site() returns false.
		$this->subscriber->shouldNotReceive( 'generate' );

		$this->subscriber->display_rocketcdn_cta( $this->buildCtaData() );
	}

	/**
	 * Builds a minimal cta_data array for the subscriber method.
	 *
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
