<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\DataManagerSubscriber;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber::set_process_status
 *
 * @group  AdminOnly
 * @group  DataManagerSubscriber
 * @group  RocketCDN
 */
class Test_SetProcessStatus extends AjaxTestCase {
	protected static $ajax_action = 'rocketcdn_process_set';

	public function testCallbackIsRegistered() {
		$this->assertCallbackRegistered( 'wp_ajax_rocketcdn_process_set', 'set_process_status' );
    }

	/**
	 * @dataProvider configTestData
	 */
    public function testShouldDoExpected( $status, $expected ) {
        $_POST['status'] = $status;

        add_option( 'rocketcdn_process', true );

        $this->callAjaxAction();

        $this->assertEquals( $expected['rocketcdn_process'], get_option( 'rocketcdn_process' ) );
    }
}
