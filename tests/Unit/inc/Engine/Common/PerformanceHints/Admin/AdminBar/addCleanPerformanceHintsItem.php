<?php

namespace WP_Rocket\tests\Unit\inc\Engine\Common\PerformanceHints\Admin\AdminBar;

use Mockery;
use WP_Admin_Bar;
use Brain\Monkey\Functions;
use WP_Rocket\Engine\Media\AboveTheFold\Factory as ATFFactory;
use WP_Rocket\Engine\Optimization\LazyRenderContent\Factory;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Common\PerformanceHints\Admin\AdminBar;

/**
 * @covers \WP_Rocket\Engine\Common\PerformanceHints\Admin\AdminBar::add_clear_performance_menu_item
 * @group  PerformanceHints
 */
class Test_AddCleanPerformanceHintsItem extends TestCase {
	private $admin_bar;
	private $atf_context;
	private $lrc_context;

	private $wp_admin_bar;
	private $factories;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/WP_Admin_Bar.php';
	}

	protected function setUp(): void {
		parent::setUp();

		$atf_factory        = $this->createMock(ATFFactory::class);
		$lrc_factory        = $this->createMock(Factory::class);
		$this->wp_admin_bar = new WP_Admin_Bar();

		$this->factories = [
			$atf_factory,
			$lrc_factory
		];
		$this->admin_bar = new AdminBar( $this->factories,'' );

		$this->stubTranslationFunctions();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		Functions\when( 'rocket_valid_key' )
			->justReturn( $config['rocket_valid_key'] );
		Functions\when( 'wp_get_environment_type' )
			->justReturn( $config['environment'] );
		Functions\when( 'is_admin' )
			->justReturn( $config['is_admin'] );

		Functions\when( 'current_user_can' )
			->justReturn( $config['current_user_can'] );

		Functions\when( 'wp_nonce_url' )->alias(
			function ( $url ) {
				return str_replace( '&', '&amp;', "{$url}&_wpnonce=123456" );
			}
		);

		Functions\when( 'admin_url' )->alias(
			function ( $path ) {
				return "http://example.org/wp-admin/{$path}";
			}
		);

		$this->admin_bar->add_clear_performance_menu_item( $this->wp_admin_bar );

		$node = $this->wp_admin_bar->get_node( 'clear-performance-hints' );

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
