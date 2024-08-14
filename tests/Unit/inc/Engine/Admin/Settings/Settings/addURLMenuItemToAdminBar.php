<?php

namespace WP_Rocket\tests\Unit\inc\Engine\Admin\Settings\Settings;

use WP_Admin_Bar;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Admin\Settings\AdminBarMenuTrait;

class Test_AddURLMenuItemToAdminBar extends TestCase {
	protected $admin_bar;

	private $wp_admin_bar;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/WP_Admin_Bar.php';
	}


	public function setUp(): void {
		parent::setUp();
		$this->admin_bar = $this->getObjectForTrait(AdminBarMenuTrait::class);
		$this->wp_admin_bar = new WP_Admin_Bar();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		Functions\expect('rocket_can_display_options')->with()->andReturn($config['display_option']);
		Functions\expect('is_admin')->with()->andReturn($config['admin']);
		Functions\when( 'wp_get_environment_type' )
			->justReturn( $config['environment'] );

		Functions\when( 'admin_url' )->alias(
			function ( $path ) {
				return "http://example.org/wp-admin/{$path}";
			}
		);

		Functions\when( 'wp_nonce_url' )->alias(
			function ( $url ) {
				return str_replace( '&', '&amp;', "{$url}&_wpnonce=123456" );
			}
		);

		$method = self::get_reflective_method( 'add_url_menu_item_to_admin_bar', get_class( $this->admin_bar ) );
		$method->invoke(
			$this->admin_bar,
			$this->wp_admin_bar,
			$config['menu_id'],
			$config['title'],
			$config['action'],
			$config['context']
		);

		$node = $this->wp_admin_bar->get_node( $config['menu_id'] );

		if ( null === $expected ) {
			$this->assertNull( $node );
			return;
		}

		$this->assertSame(
			$expected['id'],
			$node->id
		);

		$this->assertSame(
			$expected['title'],
			$node->title
		);
	}
}
