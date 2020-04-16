<?php

namespace WP_Rocket\Tests\Integration\inc\common;

use WP_Widget_Text;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers ::rocket_widget_update_callback
 * @uses   ::rocket_clean_domain
 *
 * @group  Common
 * @group  Purge
 * @group  vfs
 */
class Test_RocketWidgetUpdateCallback extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/common/rocketWidgetUpdateCallback.php';

	public static function wpSetUpBeforeClass( $factory ) {
		wp_set_current_user( $factory->user->create( [ 'role' => 'administrator' ] ) );
	}

	public function testCallbackIsRegistered() {
		$this->assertEquals( 10, has_filter( 'widget_update_callback', 'rocket_widget_update_callback' ) );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldInvokeRocketCleanDomainOnWidgetUpdate( $instance, $expected ) {
		$widget                             = new WP_Widget_Text();
		$_POST["widget-{$widget->id_base}"] = $instance;

		$shouldNotClean = $this->getNonCleaned( $expected['non_cleaned'] );

		// Update it.
		$widget->update_callback();

		// Check the "cleaned" directories.
		foreach ( $expected['cleaned'] as $dir => $contents ) {
			// Deleted.
			if ( is_null( $contents ) ) {
				$this->assertFalse( $this->filesystem->exists( $dir ) );
			} else {
				$shouldNotClean[] = trailingslashit( $dir );
				// Emptied, but not deleted.
				$this->assertSame( $contents, $this->filesystem->getFilesListing( $dir ) );
			}
		}

		// Check the non-cleaned files/directories still exist.
		$entriesAfterCleaning = $this->filesystem->getListing( $this->filesystem->getUrl( $this->config['vfs_dir'] ) );
		$actual = array_diff( $entriesAfterCleaning, $shouldNotClean );
		if ( ! empty( $expected['test_it'] ) ) {
			var_dump( $actual );
		} else {
			$this->assertEmpty( $actual );
		}
	}

	private function getNonCleaned( $config ) {
		$entries = [];
		foreach( $config as $entry => $scanDir ) {
			$entries[] = $entry;
			if ( $scanDir && $this->filesystem->is_dir( $entry ) ) {
				$entries = array_merge( $entries, $this->filesystem->getListing( $entry ) );
			}
		}
		return $entries;
	}
}
