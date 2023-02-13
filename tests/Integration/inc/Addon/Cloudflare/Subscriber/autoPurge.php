<?php

namespace WP_Rocket\Tests\Integration\inc\Addons\Cloudflare\Subscriber;

use Brain\Monkey\Functions;

/**
 * @covers \WPMedia\Cloudflare\Subscriber::auto_purge
 * @group  DoCloudflare
 * @group  Addons
 */
class Test_AutoPurge extends TestCase {

	public static function set_up_before_class() {
		parent::set_up_before_class();
		self::setApiCredentialsInOptionsWithFilter();
	}

	public function testShouldBailoutWhenUserCantPurgeCF() {
		var_dump('start_cant');

		$user = $this->factory->user->create( [ 'role' => 'contributor' ] );
		wp_set_current_user( $user );
		Functions\when('is_wp_error')->alias(function () {
			ob_start();

			debug_print_backtrace();

			$trace = ob_get_contents();

			ob_end_clean();
			echo $trace;
			return false;
		});
		///Functions\expect( 'is_wp_error' )->never();

		do_action( 'after_rocket_clean_domain' );
		var_dump('end_cant');
	}

	public function testShouldBailoutWhenNoPageRule() {
	var_dump('start');
		$admin = get_role( 'administrator' );
		$admin->add_cap( 'rocket_purge_cloudflare_cache' );
		$user = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user );
		Functions\when('is_wp_error')->alias(function () {
			ob_start();

			debug_print_backtrace();

			$trace = ob_get_contents();

			ob_end_clean();
			echo $trace;
			return false;
		});
		/*Functions\expect( 'is_wp_error' )
			->ordered()
			->once()
			->with( null )
			->andAlsoExpectIt()
			->once()
			->with( 0 );*/

		do_action( 'after_rocket_clean_domain' );
		var_dump('end');
	}
}
