<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\Admin\Subscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\AdminTestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\Admin\Subscriber::add_regenerate_menu_item
 *
 * @group  AdminOnly
 * @group  CriticalPath
 * @group  CriticalPathAdminSubscriber
 */
class Test_AddRegenerateMenuItem extends AdminTestCase {
	use ProviderTrait;

	protected static $class_name = 'Admin';
	protected $user_id = 0;
	private $filter;
	private $option;

	public function setUp() {
		parent::setUp();

		$this->setRoleCap( 'administrator', 'rocket_regenerate_critical_css' );

		add_filter( 'show_admin_bar', [ $this, 'return_true'] );
		add_filter( 'pre_get_rocket_option_async_css', [ $this, 'async_css'] );
		add_filter( 'do_rocket_critical_css_generation', [ $this, 'filter_generation' ] );
	}

	public function tearDown() {
		parent::tearDown();

		$this->removeRoleCap( 'administrator', 'rocket_regenerate_critical_css' );

		remove_filter( 'show_admin_bar', [ $this, 'return_true'] );
		remove_filter( 'pre_get_rocket_option_async_css', [ $this, 'async_css'] );
		remove_filter( 'do_rocket_critical_css_generation', [ $this, 'filter_generation' ] );

		unset( $_SERVER['REQUEST_URI' ] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $cap, $admin, $option, $filter, $request, $expected ) {
		global $wp_admin_bar;

		if( $cap ) {
			$this->setCurrentUser( 'administrator' );
		} else {
			$this->setCurrentUser( 'editor' );
		}

		Functions\when( 'is_admin' )->justReturn( $admin );

		$this->option = $option;
		$this->filter = $filter;

		$_SERVER['REQUEST_URI' ] = $request;

		$this->fireAdminInit();
		do_action_ref_array( 'admin_bar_menu', array( &$wp_admin_bar )  );

		if ( false === $expected ) {
			$this->assertNull( $wp_admin_bar->get_node( 'regenerate-critical-path') );
		} else {
			$this->assertEquals(
				$wp_admin_bar->get_node( 'regenerate-critical-path' ),
				$expected
			);
		}
	}

	public function async_css() {
		return $this->option;
	}

	public function filter_generation() {
		return $this->filter;
	}
}