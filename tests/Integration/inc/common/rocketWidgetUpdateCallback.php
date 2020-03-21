<?php

namespace WP_Rocket\Tests\Integration\inc\common;

use Brain\Monkey\Functions;
use WP_Widget_Text;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers ::rocket_widget_update_callback
 * @uses   ::rocket_clean_domain
 * @group Common
 * @group Purge
 */
class Test_RocketWidgetUpdateCallback extends FilesystemTestCase {
	private $root_dir = 'vfs://cache/wp-rocket/';
	private $domain_dir = 'vfs://cache/wp-rocket/example.org';
	private $dirs = [
		'vfs://cache/wp-rocket/example.org/about',
		'vfs://cache/wp-rocket/example.org/blog',
		'vfs://cache/wp-rocket/example.org/category/wordpress',
		'vfs://cache/wp-rocket/example.org/en',
	];

	public static function wpSetUpBeforeClass( $factory ) {
		wp_set_current_user( $factory->user->create( [ 'role' => 'administrator' ] ) );
	}

	public function setUp() {
		parent::setUp();

		// This is necessary as glob() is not compatible with our virtual filesystem.
		add_action( 'before_rocket_clean_domain', [ $this, 'delete_domain_callback' ] );
	}

	public function tearDown() {
		parent::tearDown();

		remove_action( 'before_rocket_clean_domain', [ $this, 'delete_domain_callback' ] );
	}

	public function testCallbackIsRegistered() {
		$this->assertEquals( 10, has_filter( 'widget_update_callback', 'rocket_widget_update_callback' ) );
	}

	/**
	 * @dataProvider addDataProvider
	 */
	public function testShouldInvokeRocketCleanDomainOnWidgetUpdate( $instance ) {
		$widget                             = new WP_Widget_Text();
		$_POST["widget-{$widget->id_base}"] = $instance;

		Functions\expect( 'rocket_get_constant' )->once()->with( 'WP_ROCKET_CACHE_PATH' )->andReturn( $this->root_dir );

		// Check that the files and directories exist before updating the widget.
		$this->assertTrue( $this->filesystem->exists( 'cache/wp-rocket/example.org/index.html' ) );
		$this->assertTrue( $this->filesystem->exists( 'cache/wp-rocket/example.org/index.html_gzip' ) );
		$this->assertTrue( $this->filesystem->exists( 'cache/wp-rocket/example.org/about/index.html' ) );
		$this->assertTrue( $this->filesystem->exists( 'cache/wp-rocket/example.org/blog/index.html' ) );
		$this->assertTrue( $this->filesystem->exists( 'cache/wp-rocket/example.org/en/index.html' ) );

		$widget->update_callback();

		// Check that rocket_clean_domain() ran by validating that all files are deleted and the directories are empty.
		$this->assertFalse( $this->filesystem->exists( 'cache/wp-rocket/example.org/index.html' ) );
		$this->assertFalse( $this->filesystem->exists( 'cache/wp-rocket/example.org/index.html_gzip' ) );
		foreach ( $this->dirs as $dir ) {
			$this->assertCount( 0, $this->scandir( $dir ) );
		}
	}

	public function delete_domain_callback( $root ) {
		$root = rtrim( $root, '*' );
		$this->assertSame( 'vfs://cache/wp-rocket/example.org', $root );
		$this->delete_files( $root );
	}

	public function addDataProvider() {
		return $this->getTestData( __DIR__, 'rocketWidgetUpdateCallback' );
	}
}
