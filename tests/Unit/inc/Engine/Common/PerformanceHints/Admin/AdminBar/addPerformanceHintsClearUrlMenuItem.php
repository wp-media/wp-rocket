<?php

namespace WP_Rocket\tests\Unit\inc\Engine\Common\PerformanceHints\Admin\AdminBar;

use Mockery;
use WP_Admin_Bar;
use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\PerformanceHints\Admin\AdminBar;
use WP_Rocket\Engine\Media\AboveTheFold\Factory as ATFFactory;
use WP_Rocket\Engine\Optimization\LazyRenderContent\Factory;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Common\Context\ContextInterface;

/**
 * @covers \WP_Rocket\Engine\Common\PerformanceHints\Admin\AdminBar::add_clear_url_performance_hints_menu_item
 * @group  PerformanceHints
 */
class Test_AddPerformanceHintsClearUrlMenuItem extends TestCase {
	private $factories;
	private $wp_admin_bar;

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

		$this->stubTranslationFunctions();
	}

	protected function tearDown(): void {
		unset( $GLOBALS['post'] );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$admin_bar = new AdminBar( $config['factories'] ? $this->factories : [], '' );

		Functions\when( 'wp_get_environment_type' )
			->justReturn( $config['environment'] );
		Functions\when( 'is_admin' )
			->justReturn( $config['is_admin'] );

		$GLOBALS['post'] = $config['post'];

		Functions\when( 'rocket_can_display_options' )
			->justReturn( $config['can_display_options'] );

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

		$admin_bar->add_clear_url_performance_hints_menu_item( $this->wp_admin_bar );

		$node = $this->wp_admin_bar->get_node( 'clear-performance-hints-data-url' );

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
