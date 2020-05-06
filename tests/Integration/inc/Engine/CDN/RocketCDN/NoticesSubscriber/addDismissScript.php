<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\NoticesSubscriber;

use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber::add_dismiss_script
 * @uses ::rocket_is_live_site
 *
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

	public function testShouldDisplayNothingWhenNotLiveSite() {
		$callback = function() {
			return 'http://localhost';
		};

		add_filter( 'home_url', $callback );

		$this->assertNotContains( $this->get_script( wp_create_nonce( 'rocketcdn_dismiss_notice' ) ), $this->getActualHtml() );

		remove_filter( 'home_url', $callback );
	}

	public function testShouldNotAddScriptWhenNoCapability() {
		$user_id = self::factory()->user->create( [ 'role' => 'editor' ] );

		wp_set_current_user( $user_id );

		$this->assertNotContains( $this->get_script( wp_create_nonce( 'rocketcdn_dismiss_notice' ) ), $this->getActualHtml() );
	}

	public function testShouldNotAddScriptWhenNotRocketPage() {
		$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );

		wp_set_current_user( $user_id );
		set_current_screen( 'edit.php' );

		$this->assertNotContains( $this->get_script( wp_create_nonce( 'rocketcdn_dismiss_notice' ) ), $this->getActualHtml() );
	}

	public function testShouldNotAddScriptWhenDismissed() {
		$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );

		wp_set_current_user( $user_id );

		set_current_screen( 'settings_page_wprocket' );
		add_user_meta( get_current_user_id(), 'rocketcdn_dismiss_notice', true );

		$this->assertNotContains( $this->get_script( wp_create_nonce( 'rocketcdn_dismiss_notice' ) ), $this->getActualHtml() );
	}

	public function testShouldNotAddScriptWhenActive() {
		$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );

		wp_set_current_user( $user_id );

		set_current_screen( 'settings_page_wprocket' );
		set_transient( 'rocketcdn_status', [ 'subscription_status' => 'running' ], MINUTE_IN_SECONDS );

		$this->assertNotContains( $this->get_script( wp_create_nonce( 'rocketcdn_dismiss_notice' ) ), $this->getActualHtml() );
	}

	public function testShouldAddScriptWhenNotActive() {
		$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );

		wp_set_current_user( $user_id );

		set_current_screen( 'settings_page_wprocket' );
		set_transient( 'rocketcdn_status', [ 'subscription_status' => 'cancelled' ], MINUTE_IN_SECONDS );

		$this->assertContains( $this->get_script( wp_create_nonce( 'rocketcdn_dismiss_notice' ) ), $this->getActualHtml() );
	}
}
