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
	private $text_direction;

	protected static $transients = [
		'wp_rocket_customer_data' => null,
	];

	public function set_up() {
		global $wp_locale;

		parent::set_up();

		set_current_screen( 'settings_page_wprocket' );
		Functions\when( 'get_bloginfo' )->justReturn( '5.4' );

		$this->text_direction = $wp_locale->text_direction;
	}

	public function tear_down() {
		global $wp_locale;
		set_current_screen( 'front' );

		$wp_locale->text_direction = $this->text_direction;
		$this->white_label = false;

		remove_filter( 'rocket_beacon_locale', [ $this, 'locale_cb' ] );
		remove_filter( 'pre_get_rocket_option_consumer_email', [ $this, 'consumer_email' ] );

		parent::tear_down();
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

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnBeaconScript( $config, $expected ) {
		global $wp_locale;

		if ( true === $config['current_user_can'] ) {
			$this->createUser( 'administrator' );
			$this->assertTrue( current_user_can( 'rocket_manage_options' ) );
		} else {
			$this->createUser( 'contributor' );
			$this->assertFalse( current_user_can( 'rocket_manage_options' ) );
		}
		$this->white_label =  $config['white_label'];
		if ( null === $expected ) {
			$this->assertEmpty( $this->getActualHtml() );
			return;
		}
		$this->locale         = $config['locale'];
		$this->rocket_version = '3.6';

		if ( $config['rtl'] ) {
			$wp_locale->text_direction = 'rtl';
		}

		if ( false !== $config['customer_data'] ) {
			set_transient( 'wp_rocket_customer_data', $config['customer_data'] );
		}

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
