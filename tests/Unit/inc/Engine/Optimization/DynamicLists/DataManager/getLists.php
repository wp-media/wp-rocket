<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DynamicLists\DataManager;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists\DataManager;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\DynamicLists\DataManager::get_lists
 *
 * @group DynamicLists
 */
class test_GetLists extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/DynamicLists/DataManager/getLists.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpected( $transient, $fallback, $expected ) {
		$data_manager = new DataManager();

		Functions\when( 'get_transient' )->justReturn( $transient );

		if ( true === $fallback ) {
			$this->filesystem->delete( $this->filesystem->getUrl( 'wp-content/wp-rocket-config/dynamic-lists.json' ) );
			$this->assertFalse( $this->filesystem->exists( $this->filesystem->getUrl( 'wp-content/wp-rocket-config/dynamic-lists.json' ) ) );
		}

		if ( false === $transient ) {
			Functions\expect( 'set_transient' )
				->once()
				->with( 'wpr_dynamic_lists', Mockery::type( 'object' ), WEEK_IN_SECONDS );
		}

		$this->assertEquals(
			$expected,
			$data_manager->get_lists()
		);

		if ( true === $fallback ) {
			$this->assertTrue( $this->filesystem->exists( $this->filesystem->getUrl( 'wp-content/wp-rocket-config/dynamic-lists.json' ) ) );
		}
	}
}
