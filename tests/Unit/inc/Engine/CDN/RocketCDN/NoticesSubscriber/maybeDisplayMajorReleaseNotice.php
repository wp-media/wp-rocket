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
use WP_Rocket\Engine\Tracking\Tracking;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber::maybe_display_major_release_notice
 *
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber::maybe_display_major_release_notice
 * @group  RocketCDN
 * @group  RocketCDNNotices
 */
class Test_MaybeDisplayMajorReleaseNotice extends TestCase {

	/**
	 * @var Mockery\MockInterface|APIClient
	 */
	private $api_client;

	/**
	 * @var Mockery\MockInterface|Beacon
	 */
	private $beacon;

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
	 * @var NoticesSubscriber
	 */
	private $subscriber;

	public function set_up(): void {
		parent::set_up();

		defined( 'WP_ROCKET_PLUGIN_SLUG' ) || define( 'WP_ROCKET_PLUGIN_SLUG', 'wp-rocket' );
		defined( 'WP_ROCKET_VERSION' ) || define( 'WP_ROCKET_VERSION', '3.23' );

		Functions\stubTranslationFunctions();

		$this->api_client              = Mockery::mock( APIClient::class );
		$this->beacon                  = Mockery::mock( Beacon::class );
		$this->tracking                = Mockery::mock( Tracking::class );
		$this->options                 = Mockery::mock( Options_Data::class );
		$this->subscription_controller = Mockery::mock( SubscriptionController::class );
		$this->user                    = Mockery::mock( User::class );

		$this->subscriber = new NoticesSubscriber(
			$this->api_client,
			$this->beacon,
			$this->tracking,
			'',
			$this->options,
			$this->subscription_controller,
			$this->user
		);
	}

	/**
	 * When the rocket_hide_rocketcdn_notices filter returns true (e.g. one.com wiring
	 * `'rocket_hide_rocketcdn_notices' => 'return_true'`), the method must bail out before
	 * ever touching the RocketCDN token or rendering anything.
	 */
	public function testShouldReturnEarlyWhenNoticesAreHidden(): void {
		Functions\expect( 'apply_filters' )
			->once()
			->with( 'rocket_hide_rocketcdn_notices', false )
			->andReturn( true );

		Functions\expect( 'get_option' )->never();
		Functions\expect( 'current_user_can' )->never();

		ob_start();
		$this->subscriber->maybe_display_major_release_notice( '3.23' );
		$output = ob_get_clean();

		$this->assertSame( '', $output );
	}

	/**
	 * When the filter returns false (the default), the notice must still render for
	 * non-one.com sites — regression guard for the fix.
	 */
	public function testShouldDisplayNoticeWhenNoticesAreNotHidden(): void {
		Functions\expect( 'apply_filters' )
			->once()
			->with( 'rocket_hide_rocketcdn_notices', false )
			->andReturn( false );

		Functions\when( 'get_option' )->justReturn( '' );
		Functions\when( 'admin_url' )->justReturn( 'https://example.org/wp-admin/options-general.php' );
		Functions\when( 'wp_nonce_url' )->returnArg();
		Functions\when( 'esc_url' )->returnArg();

		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'get_current_user_id' )->justReturn( 1 );
		Functions\when( 'get_user_meta' )->justReturn( [] );
		Functions\when( 'do_action' )->justReturn( null );
		Functions\when( 'sanitize_html_class' )->returnArg();
		Functions\when( 'wp_create_nonce' )->justReturn( 'nonce' );
		Functions\when( 'esc_js' )->returnArg();

		$notice_data = null;

		Functions\expect( 'rocket_notice_html' )
			->once()
			->with(
				Mockery::on(
					static function ( $data ) use ( &$notice_data ) {
						$notice_data = $data;
						return is_array( $data );
					}
				)
			);

		ob_start();
		$this->subscriber->maybe_display_major_release_notice( '3.23' );
		ob_get_clean();

		$this->assertSame( 'rocket_major_release_notice_3_23', $notice_data['dismiss_button'] );
	}
}
