<?php

namespace WP_Rocket\Tests\Integration\Subscriber\CDN\RocketCDN;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\NoticesSubscriber::add_dismiss_script
 * @group  RocketCDN
 * @group  AdminOnly
 */
class Test_AddDismissScript extends TestCase {
	private function get_script( $nonce ) {
		return $this->format_the_html( "<script>
		window.addEventListener( 'load', function() {
			var dismissBtn  = document.querySelectorAll( '#rocketcdn-promote-notice .notice-dismiss, #rocketcdn-promote-notice #rocketcdn-learn-more-dismiss' );

			dismissBtn.forEach(function(element) {
				element.addEventListener( 'click', function( event ) {
					var httpRequest = new XMLHttpRequest(),
						postData    = '';

					postData += 'action=rocketcdn_dismiss_notice';
					postData += '&nonce=" . esc_attr( $nonce ) . "';
					httpRequest.open( 'POST', 'http://example.org/wp-admin/admin-ajax.php' );
					httpRequest.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' )
					httpRequest.send( postData );
				});
			});
		});
		</script>" );
	}

	private function getActualHtml() {
		ob_start();
		do_action( 'admin_footer' );

		return $this->format_the_html( ob_get_clean() );
	}

	/**
	 * Test should not add script when user doesn't have the capability to use it
	 */
	public function testShouldNotAddScriptWhenNoCapability() {
		$user_id = self::factory()->user->create( [ 'role' => 'editor' ] );

		wp_set_current_user( $user_id );

		$this->assertNotContains( $this->get_script( wp_create_nonce( 'rocketcdn_dismiss_notice' ) ), $this->getActualHtml() );
	}

	/**
	 * Test should not add script when not on WP Rocket settings page
	 */
	public function testShouldNotAddScriptWhenNotRocketPage() {
		$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );

		wp_set_current_user( $user_id );
		set_current_screen( 'edit.php' );

		$this->assertNotContains( $this->get_script( wp_create_nonce( 'rocketcdn_dismiss_notice' ) ), $this->getActualHtml() );
	}

	/**
	 * Test should not add script when the notice has been dismissed
	 */
	public function testShouldNotAddScriptWhenDismissed() {
		$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );

		wp_set_current_user( $user_id );
	
		set_current_screen( 'settings_page_wprocket' );
		add_user_meta( get_current_user_id(), 'rocketcdn_dismiss_notice', true );

		$this->assertNotContains( $this->get_script( wp_create_nonce( 'rocketcdn_dismiss_notice' ) ), $this->getActualHtml() );
	}

	/**
	 * Test should not add script when RocketCDN is active
	 */
	public function testShouldNotAddScriptWhenActive() {
		$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );

		wp_set_current_user( $user_id );
	
		set_current_screen( 'settings_page_wprocket' );
		set_transient( 'rocketcdn_status', [ 'subscription_status' => 'running' ], MINUTE_IN_SECONDS );

		$this->assertNotContains( $this->get_script( wp_create_nonce( 'rocketcdn_dismiss_notice' ) ), $this->getActualHtml() );
	}

	/**
	 * Test should add script when RocketCDN is inactive
	 */
	public function testShouldAddScriptWhenNotActive() {
		$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );

		wp_set_current_user( $user_id );
	
		set_current_screen( 'settings_page_wprocket' );
		set_transient( 'rocketcdn_status', [ 'subscription_status' => 'cancelled' ], MINUTE_IN_SECONDS );

		$this->assertContains( $this->get_script( wp_create_nonce( 'rocketcdn_dismiss_notice' ) ), $this->getActualHtml() );
	}
}