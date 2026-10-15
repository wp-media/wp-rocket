<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\Admin\SwitchToRucssBanner;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CriticalPath\Admin\SwitchToRucssBanner::add_banner
 *
 * @group AdminOnly
 * @group CriticalPath
 */
class Test_AddBanner extends TestCase {
	private static $user;
	private static $user_id = 0;
	private $original_user;
	protected $config = [];

	public static function wpSetUpBeforeClass( $factory ) {
		self::$user_id = $factory->user->create( [ 'role' => 'administrator' ] );
	}

	public static function set_up_before_class() {
		parent::set_up_before_class();

		$container  = apply_filters( 'rocket_container', null );
		self::$user = $container->get( 'user' );
	}

	public function set_up() {
		parent::set_up();

		add_filter( 'pre_get_rocket_option_async_css', [ $this, 'async_css' ] );
		add_filter( 'rocket_disable_rucss_setting', [ $this, 'rucss' ] );

		wp_set_current_user( self::$user_id );

		$this->original_user = $this->getNonPublicPropertyValue( 'user', self::$user, self::$user );

		$this->unregisterAllCallbacksExcept( 'rocket_dashboard_sidebar_license', 'add_banner', 14 );
	}

	public function tear_down() {
		$this->set_reflective_property( $this->original_user, 'user', self::$user );

		remove_filter( 'pre_get_rocket_option_async_css', [ $this, 'async_css' ] );
		remove_filter( 'rocket_disable_rucss_setting', [ $this, 'rucss' ] );
		update_user_meta( self::$user_id, 'rocket_boxes', [] );
		$this->restoreWpHook( 'rocket_dashboard_sidebar_license' );
		$this->white_label = false;

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->config = $config;
		$this->set_reflective_property( $config['user'], 'user', self::$user );

		if ( $config['in_boxes'] ) {
			rocket_dismiss_box( 'switch_to_rucss_notice' );
		}

		$this->white_label = $config['white_label'] ?? false;

		$html = apply_filters( 'rocket_dashboard_sidebar_license', '' );

		if ( $expected ) {
			$this->assertStringContainsString( 'wpr-rucss-switch', $html );
			$this->assertStringContainsString( 'action=switch_to_rucss', $html );
			$this->assertStringContainsString( 'box=switch_to_rucss_notice', $html );
			$this->assertStringNotContainsString( 'wpr-notice', $html );
		} else {
			$this->assertSame( '', $html );
		}
	}

	public function testShouldReturnExistingHtmlUnchanged() {
		$this->config = [
			'async_css'    => true,
			'rucss_status' => [ 'disable' => false ],
		];

		$this->assertSame( '<div>license</div>', apply_filters( 'rocket_dashboard_sidebar_license', '<div>license</div>' ) );
	}

	public function async_css() {
		return $this->config['async_css'];
	}

	public function rucss() {
		return $this->config['rucss_status'];
	}
}
