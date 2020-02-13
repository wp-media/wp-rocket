<?php

namespace WP_Rocket\Tests\Integration\Subscriber\CDN\RocketCDN\DataManagerSubscriber;

use WPMedia\PHPUnit\Integration\AjaxTestCase;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\DataManagerSubscriber::set_process_status
 * @group  RocketCDN
 * @group  AdminOnly
 */
class Test_SetProcessStatus extends AjaxTestCase {
	public function setUp() {
		parent::setUp();

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
