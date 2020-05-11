<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Beacon\Beacon;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\Admin\Beacon\Beacon::insert_script
 * @group  Beacon
 * @group  AdminOnly
 */
class Test_InsertScript extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Admin/Beacon/Beacon/insert-script.php';
	private function getActualHtml() {
		ob_start();
		do_action( 'admin_print_footer_scripts-settings_page_wprocket' );

		return $this->format_the_html( ob_get_clean() );
	}

	public function testShouldReturNullWhenNoCapacity() {
		$user = $this->factory->user->create( [ 'role' => 'contributor' ] );
		wp_set_current_user( $user );

		set_current_screen( 'settings_page_wprocket' );

		$this->assertNotContains( 'Beacon', $this->getActualHtml() );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnBeaconScript( $locale, $expected ) {
		$admin = get_role( 'administrator' );
		$admin->add_cap( 'rocket_manage_options' );

		$user = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user );

		set_current_screen( 'settings_page_wprocket' );

		$locale_cb = function() use ( $locale ) {
			return current( array_slice( explode( '_', $locale ), 0, 1 ) );
		};
		add_filter( 'rocket_beacon_locale', $locale_cb );

		Functions\when( 'get_bloginfo' )->justReturn( '5.4' );
		Functions\when( 'rocket_get_constant' )->justReturn( '3.6' );

		add_filter( 'pre_get_rocket_option_consumer_email', [ $this, 'consumer_email' ] );

		$this->assertSame(
			$this->format_the_html( $expected ),
			$this->getActualHtml()
		);

		remove_filter( 'rocket_beacon_locale', $locale_cb );
		remove_filter( 'pre_get_rocket_option_consumer_email', [ $this, 'consumer_email' ] );
	}

	public function consumer_email() {
		return 'dummy@wp-rocket.me';
	}
}
