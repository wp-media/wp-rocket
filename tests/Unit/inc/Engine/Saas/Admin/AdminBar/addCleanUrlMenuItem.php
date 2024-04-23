<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Saas\Admin\AdminBar;

use Mockery;
use Brain\Monkey\Functions;
use WP_Admin_Bar;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Saas\Admin\AdminBar;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Saas\Admin\AdminBar::add_clean_url_menu_item
 * @group  Saas
 */
class Test_AddCleanUrlMenuItem extends TestCase {
	private $admin_bar;
	private $options;
	private $atf_context;
	private $rucss_url_context;
	private $wp_admin_bar;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/WP_Admin_Bar.php';
	}

	protected function setUp(): void {
		parent::setUp();

		$this->options           = Mockery::mock( Options_Data::class );
		$this->atf_context       = Mockery::mock( ContextInterface::class );
		$this->rucss_url_context = Mockery::mock( ContextInterface::class );
		$this->admin_bar         = new AdminBar( $this->options, $this->atf_context, $this->rucss_url_context, '' );
		$this->wp_admin_bar      = new WP_Admin_Bar();

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
		Functions\when( 'wp_get_environment_type' )
			->justReturn( $config['environment'] );
		Functions\when( 'is_admin' )
			->justReturn( $config['is_admin'] );

		$GLOBALS['post'] = $config['post'];

		Functions\when( 'rocket_can_display_options' )
			->justReturn( $config['can_display_options'] );

		$this->atf_context->shouldReceive( 'is_allowed' )
			->andReturn( $config['atf_context'] );

		$this->rucss_url_context->shouldReceive( 'is_allowed' )
			->andReturn( $config['rucss_context'] );

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

		$this->admin_bar->add_clean_url_menu_item( $this->wp_admin_bar );

		$node = $this->wp_admin_bar->get_node( 'clear-saas-url' );

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
