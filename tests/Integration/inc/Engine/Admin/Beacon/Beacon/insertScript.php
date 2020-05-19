<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\Beacon\Beacon;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Admin\Beacon\Beacon::insert_script
 *
 * @group  Beacon
 * @group  AdminOnly
 */
class Test_InsertScript extends TestCase {
	private $locale;

	public function setUp() {
		parent::setUp();

		set_current_screen( 'settings_page_wprocket' );
		Functions\when( 'get_bloginfo' )->justReturn( '5.4' );
	}

	public function tearDown() {
		parent::tearDown();

		set_current_screen( 'front' );

		remove_filter( 'rocket_beacon_locale', [ $this, 'locale_cb' ] );
		remove_filter( 'pre_get_rocket_option_consumer_email', [ $this, 'consumer_email' ] );
	}

	public function testCallbackIsRegistered() {
		global $wp_filter;

		$this->assertArrayHasKey( 'admin_print_footer_scripts-settings_page_wprocket', $wp_filter );
		$callbacks = $wp_filter['admin_print_footer_scripts-settings_page_wprocket']->callbacks;
		foreach ( $callbacks[10] as $config ) {
			$this->assertInstanceOf( Beacon::class, $config['function'][0] );
			$this->assertSame( 'insert_script', $config['function'][1] );
		}
	}

	public function testShouldReturNullWhenNoCapacity() {
		$this->createUser( 'contributor' );
		$this->assertFalse( current_user_can( 'rocket_manage_options' ) );

		$this->assertNotContains( 'Beacon', $this->getActualHtml() );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnBeaconScript( $locale, $expected ) {
		$this->createUser( 'administrator' );
		$this->assertTrue( current_user_can( 'rocket_manage_options' ) );

		$this->locale         = $locale;
		$this->rocket_version = '3.6';

		add_filter( 'rocket_beacon_locale', [ $this, 'locale_cb' ] );
		add_filter( 'pre_get_rocket_option_consumer_email', [ $this, 'consumer_email' ] );

		$this->assertSame(
			$this->format_the_html( $expected['script'] ),
			$this->getActualHtml()
		);
	}

	public function locale_cb() {
		return current( array_slice( explode( '_', $this->locale ), 0, 1 ) );
	}

	public function consumer_email() {
		return 'dummy@wp-rocket.me';
	}


	private function getActualHtml() {
		ob_start();
		do_action( 'admin_print_footer_scripts-settings_page_wprocket' );
		$actual = ob_get_clean();

		return empty( $actual )
			? $actual
			: $this->format_the_html( $actual );
	}

	private function createUser( $role ) {
		if ( 'administrator' === $role ) {
			$admin = get_role( 'administrator' );
			$admin->add_cap( 'rocket_manage_options' );
		}

		$user = $this->factory->user->create( [ 'role' => $role ] );
		wp_set_current_user( $user );
	}
}
