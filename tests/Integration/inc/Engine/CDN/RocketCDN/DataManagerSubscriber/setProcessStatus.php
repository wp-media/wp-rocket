<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\DataManagerSubscriber;

use WPMedia\PHPUnit\Integration\AjaxTestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber::set_process_status
 * @group  RocketCDN
 * @group  AdminOnly
 */
class Test_SetProcessStatus extends AjaxTestCase {
    /**
	 * User's ID.
	 * @var int
	 */
	private static $user_id = 0;

	/**
	 * Set up the User ID before tests start.
	 */
	public static function wpSetUpBeforeClass( $factory ) {
		self::$user_id = $factory->user->create( [ 'role' => 'administrator' ] );
    }

	public function setUp() {
		parent::setUp();

        wp_set_current_user( self::$user_id );
		$_POST['nonce'] = wp_create_nonce( 'rocket-ajax' );
		$this->action   = 'rocketcdn_process_set';
    }

    /**
	 * Test that the callback is registered to the action.
	 */
	public function testCallbackIsRegistered() {
		$this->assertTrue( has_action( 'wp_ajax_rocketcdn_process_set' ) );

		global $wp_filter;
		$obj = $wp_filter['wp_ajax_rocketcdn_process_set'];
		$callback_registration = current( $obj->callbacks[10] );
		$this->assertEquals( 'set_process_status', $callback_registration['function'][1] );
    }

    public function testShouldDoNothingWhenStatusEmpty() {
        $_POST['action'] = 'rocketcdn_process_set';
        $_POST['status'] = '';

        add_option( 'rocketcdn_process', true );

        $this->callAjaxAction();

        $this->assertTrue( get_option( 'rocketcdn_process' ) );
    }

    public function testShouldDeleteOptionWhenStatusFalse() {
        $_POST['action'] = 'rocketcdn_process_set';
        $_POST['status'] = 'false';

        add_option( 'rocketcdn_process', true );

        $this->callAjaxAction();

        $this->assertFalse( get_option( 'rocketcdn_process' ) );
    }

    public function testShouldUpdateOptionWhenStatusTrue() {
        $_POST['action'] = 'rocketcdn_process_set';
        $_POST['status'] = 'true';

        $this->callAjaxAction();

        $this->assertTrue( get_option( 'rocketcdn_process' ) );
	}
}
