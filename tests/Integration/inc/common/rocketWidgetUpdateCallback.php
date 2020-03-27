<?php

namespace WP_Rocket\Tests\Integration\inc\common;

use WP_Rocket\Tests\GlobTrait;
use WP_Widget_Text;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers ::rocket_widget_update_callback
 * @uses   ::rocket_clean_domain
 * @group  Common
 * @group  Purge
 * @group  vfs
 */
class Test_RocketWidgetUpdateCallback extends FilesystemTestCase {
	use GlobTrait;
	protected $path_to_test_data = '/inc/common/rocketWidgetUpdateCallback.php';

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
	 * @dataProvider providerTestData
	 */
	public function testShouldInvokeRocketCleanDomainOnWidgetUpdate( $instance ) {
		$widget                             = new WP_Widget_Text();
		$_POST["widget-{$widget->id_base}"] = $instance;

		// Check that the files and directories exist before updating the widget.
		foreach ( $this->original_files as $file ) {
			$this->assertTrue( $this->filesystem->exists( $file ) );
		}

		// Update it.
		$widget->update_callback();

		// Check that rocket_clean_domain() ran by validating that all files are deleted and the directories are empty.
		$listing = array_map(
			function ( $item ) {
				// Strip off the absolute path and ending directory separator.
				$item = str_replace( 'vfs://public/', '', $item );

				return untrailingslashit( $item );
			},
			$this->filesystem->getListing( $this->config['vfs_dir'] )
		);
		$this->assertSame( $this->original_dirs, $listing );
	}

	public function delete_domain_callback( $root ) {
		$root = rtrim( $root, '*' );
		$this->assertSame( 'vfs://public/wp-content/cache/wp-rocket/example.org', $root );
		$this->deleteFiles( $root, $this->filesystem );
	}
}
