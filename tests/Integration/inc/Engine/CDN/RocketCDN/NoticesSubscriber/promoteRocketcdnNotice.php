<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\NoticesSubscriber;

use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber::promote_rocketcdn_notice
 *
 * @uses ::rocket_is_live_site
 * @uses \WP_Rocket\Abstract_Render::generate
 * @uses ::rocket_direct_filesystem
 *
 * @group AdminOnly
 * @group RocketCDN
 */
class Test_PromoteRocketcdnNotice extends TestCase {
	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'admin_notices', 'promote_rocketcdn_notice' );
	}

	public function tear_down() {
		parent::tear_down();

		$this->restoreWpHook( 'admin_notices' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDisplayPerData( $data, $expected, $config ) {
		$this->white_label = isset( $config['white_label'] ) ? $config['white_label'] : $this->white_label;

		if ( isset( $config['home_url'] ) ) {
			$this->home_url = $config['home_url'];
			add_filter( 'home_url', [ $this, 'home_url_cb' ] );
		}

		if ( isset( $data['rocketcdn_status'] ) ) {
			set_transient( 'rocketcdn_status', $data['rocketcdn_status'], MINUTE_IN_SECONDS );
		}

		if ( isset( $config['role'] ) ) {
			$this->configUser( $config['role'] );
		}

		if ( isset( $config['screen'] ) ) {
			set_current_screen( $config['screen'] );
		}

		if ( isset( $config['user_meta'] ) ) {
			add_user_meta( get_current_user_id(), 'rocketcdn_dismiss_notice', $config['user_meta'] );
		}

		if ( $expected['should_display'] ) {
			$this->assertStringContainsStringIgnoringCase(
				$this->format_the_html( $this->config['notice'] ),
				$this->get_actual_html()
			);
		} else {
			$this->assertStringNotContainsStringIgnoringCase(
				$this->format_the_html( $this->config['notice'] ),
				$this->get_actual_html()
			);
		}
	}

	private function configUser( $role ) {
		// Make sure the capability is correct.
		$admin = get_role( 'administrator' );
		if ( ! $admin->has_cap( 'rocket_manage_options' ) ) {
			$admin->add_cap( 'rocket_manage_options' );
		}

		$user_id = $this->factory->user->create( [ 'role' => $role ] );
		wp_set_current_user( $user_id );
	}

	private function get_actual_html() {
		ob_start();
		do_action( 'admin_notices' );

		return $this->format_the_html( ob_get_clean() );
	}
}
