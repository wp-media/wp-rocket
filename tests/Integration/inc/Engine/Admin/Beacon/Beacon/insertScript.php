<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\Beacon\Beacon;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Admin\Beacon\Beacon::insert_script
 *
 * @group  Beacon
 * @group  AdminOnly
 */
class Test_InsertScript extends TestCase {
	private $locale;
	private static $had_cap = false;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		$admin = get_role( 'administrator' );
		self::$had_cap = $admin->has_cap( 'rocket_manage_options' );

		if ( ! self::$had_cap ) {
			$admin->add_cap( 'rocket_manage_options' );
		}
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		$admin = get_role( 'administrator' );
		if ( ! self::$had_cap ) {
			$admin->remove_cap( 'rocket_manage_options' );
		}
	}

	public function tearDown() {
		parent::tearDown();

		remove_filter( 'rocket_beacon_locale', [ $this, 'locale_cb' ] );
		remove_filter( 'pre_get_rocket_option_consumer_email', [ $this, 'consumer_email' ] );
	}

	private function getActualHtml() {
		ob_start();
		do_action( 'admin_print_footer_scripts-settings_page_wprocket' );

		return $this->format_the_html( ob_get_clean() );
	}

	private function createUser( $role ) {
		$user = $this->factory->user->create( [ 'role' => $role ] );
		wp_set_current_user( $user );
	}

	public function testShouldReturNullWhenNoCapacity() {
		$this->createUser( 'contributor' );

		set_current_screen( 'settings_page_wprocket' );

		$this->assertNotContains( 'Beacon', $this->getActualHtml() );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnBeaconScript( $locale, $expected ) {
		$this->createUser( 'administrator' );
		set_current_screen( 'settings_page_wprocket' );

		$this->locale = $locale;
		add_filter( 'rocket_beacon_locale', [ $this, 'locale_cb' ] );

		Functions\when( 'get_bloginfo' )->justReturn( '5.4' );
		Functions\when( 'rocket_get_constant' )->justReturn( '3.6' );

		add_filter( 'pre_get_rocket_option_consumer_email', [ $this, 'consumer_email' ] );

		$this->assertSame(
			$this->format_the_html( $expected ),
			$this->getActualHtml()
		);
	}

	public function locale_cb() {
		return current( array_slice( explode( '_', $this->locale ), 0, 1 ) );
	}

	public function consumer_email() {
		return 'dummy@wp-rocket.me';
	}
}
