<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\CriticalCSSSubscriber;

use WPDieException;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber::init_critical_css_generation
 * @uses   \WP_Rocket\Engine\CriticalPath\CriticalCss::process_handler
 * @uses   \WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration::cancel_process
 * @uses   ::rocket_get_constant
 *
 * @group  AdminOnly
 * @group  Subscribers
 * @group  CriticalPath
 * @group InitGeneration
 */
class Test_InitCriticalCssGeneration extends TestCase {
	protected static $transients = [
		'rocket_critical_css_generation_triggered' => null,
	];
	protected $user_id = 0;

	public function set_up() {
		parent::set_up();

		$role = get_role( 'administrator' );
		$role->add_cap( 'rocket_regenerate_critical_css' );

		unset( $_GET['_wpnonce'] );
	}

	public function tear_down() {
		if ( $this->user_id > 0 ) {
			wp_delete_user( $this->user_id );
		}

		$role = get_role( 'administrator' );
		$role->remove_cap( 'rocket_regenerate_critical_css' );

		unset( $_GET['_wpnonce'] );

		remove_filter( 'pre_get_rocket_option_do_caching_mobile_files', [ $this, 'return_true' ] );
		remove_filter( 'pre_get_rocket_option_async_css_mobile', [ $this, 'return_true' ] );
		remove_filter( 'wp_safe_redirect', [ $this, 'return_empty_string' ] );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		if ( isset( $config['nonce'] ) ) {
			$_GET['_wpnonce'] = wp_create_nonce( $config['nonce'] );
		}

		if ( ! $config['cap'] ) {
			$this->user_id = $this->factory->user->create( [ 'role' => 'editor' ] );
		} else {
			$this->user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		}

		wp_set_current_user( $this->user_id );

		add_filter( 'wp_safe_redirect', [ $this, 'return_empty_string' ] );

		if ( $config['mobile'] ) {
			add_filter( 'pre_get_rocket_option_do_caching_mobile_files', [ $this, 'return_true' ] );
			add_filter( 'pre_get_rocket_option_async_css_mobile', [ $this, 'return_true' ] );
		}

		if ( $config['referer'] ) {
			$_REQUEST['_wp_http_referer'] = addslashes( $config['referer'] );
			$_SERVER['REQUEST_URI']       = $_REQUEST['_wp_http_referer'];
		}

		$this->expectException( WPDieException::class );

		do_action( 'admin_post_rocket_purge_rocketcdn' );

		if ( ! empty( $config['referer'] ) && false === strpos( $config['referer'], 'wprocket' ) ) {
			$this->assertSame(
				1,
				get_transient( 'rocket_critical_css_generation_triggered' )
			);
		} else {
			$this->assertFalse( get_transient( 'rocket_critical_css_generation_triggered' ) );
		}
	}

	public function return_empty_string() {
		return '';
	}
}
