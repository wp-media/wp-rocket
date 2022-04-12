<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\NoticesSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber::add_dismiss_script
 * @uses   ::rocket_is_live_site
 *
 * @group  AdminOnly
 * @group  RocketCDN
 */
class Test_AddDismissScript extends TestCase {
	protected $user_id = 0;
	protected $cap;

	public function set_up() {
		parent::set_up();

		Functions\when( 'wp_create_nonce' )->justReturn( 'wp_rocket_nonce' );

		add_filter( 'home_url', [ $this, 'home_url_cb' ] );
	}

	public function tear_down() {
		delete_transient( 'rocketcdn_status' );
		delete_user_meta( get_current_user_id(), 'rocketcdn_dismiss_notice' );

		set_current_screen( 'front' );
		if ( $this->user_id > 0 ) {
			wp_delete_user( $this->user_id );
		}

		$this->removeRoleCap( 'administrator', $this->cap );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDisplayExpected( $config, $expected ) {
		$this->white_label = isset( $config['white_label'] ) ? $config['white_label'] : $this->white_label;
		$this->home_url    = $config['home_url'];
		$this->cap         = isset( $config['cap'] ) ? $config['cap'] : null;

		if ( isset( $config['cap'] ) ) {
			$this->setRoleCap( 'administrator', $config['cap'] );
		}

		if ( isset( $config['role'] ) ) {
			$this->setCurrentUser( $config['role'] );
		}

		if ( isset( $config['screen'] ) ) {
			set_current_screen( $config['screen'] );
		}

		if ( isset( $config['dismissed'] ) ) {
			add_user_meta( get_current_user_id(), 'rocketcdn_dismiss_notice', true );
		}

		if ( isset( $config['transient'] ) ) {
			set_transient( 'rocketcdn_status', $config['transient'], MINUTE_IN_SECONDS );
		}

		ob_start();
		do_action( 'admin_footer' );
		$actual = ob_get_clean();

		if ( ! empty ( $expected ) ) {
			$expected = $this->format_the_html( $expected );
		}

		if ( ! empty ( $actual ) ) {
			$actual = $this->format_the_html( $actual );
		}

		$this->assertSame( $expected, $actual );
	}

	protected function setRoleCap( $role_type, $cap ) {
		$role = get_role( $role_type );
		$role->add_cap( $cap );
	}

	protected function removeRoleCap( $role_type, $cap ) {
		$role = get_role( $role_type );
		$role->remove_cap( $cap );
	}

	protected function setCurrentUser( $role ) {
		$this->user_id = $this->factory->user->create( [ 'role' => $role ] );
		wp_set_current_user( $this->user_id );
	}
}
