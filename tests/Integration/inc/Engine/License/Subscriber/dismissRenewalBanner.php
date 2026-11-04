<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\License\Subscriber;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\License\API\Pricing;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\License\Renewal;
use WP_Rocket\Tests\Integration\AjaxTestCase;
use WP_Rocket\Tests\Integration\IsolateHookTrait;

/**
 * Test class covering \WP_Rocket\Engine\License\Subscriber::dismiss_renewal_banner
 *
 * @group  AdminOnly
 * @group  License
 */
class Test_DismissRenewalBanner extends AjaxTestCase {
	use IsolateHookTrait;

	/**
	 * User's ID.
	 * @var int
	 */
	private static $user_id = 0;

	/**
	 * Set up the User ID before tests start.
	 */
	public static function wpSetUpBeforeClass( $factory ) {
		$role = get_role( 'administrator' );
		$role->add_cap( 'rocket_manage_options' );

		self::$user_id = $factory->user->create( [ 'role' => 'administrator' ] );
	}

	public function set_up() {
		parent::set_up();

		wp_set_current_user( self::$user_id );
		$this->action = 'rocket_dismiss_renewal';

		$this->unregisterAllCallbacks( 'admin_init' );
	}

	public function tear_down() {
		delete_transient( 'rocket_renewal_banner_' . self::$user_id );
		$this->restoreWpHook( 'admin_init' );

		parent::tear_down();
	}

	public function testCallbackIsRegistered() {
		$this->assertTrue( has_action( 'wp_ajax_rocket_dismiss_renewal' ) );

		global $wp_filter;
		$obj = $wp_filter['wp_ajax_rocket_dismiss_renewal'];
		$callback_registration = current( $obj->callbacks[10] );
		$this->assertEquals( 'dismiss_renewal_banner', $callback_registration['function'][1] );
	}

	public function testShouldSetTransientWhenValid() {
		$_POST['nonce']  = wp_create_nonce( 'rocket-ajax' );
		$_POST['action'] = 'rocket_dismiss_renewal';

		$this->callAjaxAction();

		$this->assertTrue( (bool) get_transient( 'rocket_renewal_banner_' . self::$user_id ) );
	}

	/**
	 * @dataProvider provideCustomerTypes
	 */
	public function testShouldFireDismissedHookWithTheBannerType( $is_trial, $expected ) {
		$user = Mockery::mock( User::class );
		$user->shouldReceive( 'is_trial_customer' )->andReturn( $is_trial );

		$renewal = new Renewal(
			Mockery::mock( Pricing::class ),
			$user,
			Mockery::mock( Options_Data::class ),
			'views'
		);

		global $wp_filter;
		$original_hook = $wp_filter['wp_ajax_rocket_dismiss_renewal'];
		remove_all_actions( 'wp_ajax_rocket_dismiss_renewal' );
		add_action( 'wp_ajax_rocket_dismiss_renewal', [ $renewal, 'dismiss_renewal_expired_banner' ] );

		$banners = [];
		add_action(
			'rocket_dashboard_banner_dismissed',
			function ( $banner ) use ( &$banners ) {
				$banners[] = $banner;
			}
		);

		$_POST['nonce']  = wp_create_nonce( 'rocket-ajax' );
		$_POST['action'] = 'rocket_dismiss_renewal';

		$response = $this->callAjaxAction();

		$wp_filter['wp_ajax_rocket_dismiss_renewal'] = $original_hook;

		$this->assertTrue( $response->success );
		$this->assertSame( [ $expected ], $banners );
		$this->assertTrue( (bool) get_transient( 'rocket_renewal_banner_' . self::$user_id ) );
	}

	public function provideCustomerTypes() {
		return [
			'trial customer'     => [ true, 'trial_expired' ],
			'non-trial customer' => [ false, 'license_expired' ],
		];
	}
}
