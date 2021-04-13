<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\CapTrait;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber::clean_used_css_and_cache
 *
 * @uses   ::rocket_clean_domain
 *
 * @group  RUCSS
 */
class Test_CleanUsedCssAndCache extends FilesystemTestCase {
	use DBTrait;
	use CapTrait;

	private        $input;
	private static $user_id;

	protected $path_to_test_data = '/inc/Engine/Optimization/RUCSS/Admin/Subscriber/cleanUsedCssAndCache.php';

	public static function setUpBeforeClass(): void {
		self::installFresh();

		parent::setUpBeforeClass();

		CapTrait::hasAdminCapBeforeClass();
		CapTrait::setAdminCap();

		self::$user_id = static::factory()->user->create( [ 'role' => 'administrator' ] );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::uninstallAll();
	}

	public function tearDown() : void {
		remove_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );

		parent::tearDown();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $input ){
		wp_set_current_user( static::$user_id );

		$container              = apply_filters( 'rocket_container', null );
		$rucss_usedcss_query   = $container->get( 'rucss_used_css_query' );

		$this->input = $input;
		add_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );

		foreach ( $input['items'] as $item ) {
			$rucss_usedcss_query->add_item( $item );
		}
		$result = $rucss_usedcss_query->query();

		$this->assertCount( count( $input['items'] ), $result );

		// Test that cache Files are available.
		foreach ( $input['cache_files'] as $file => $content ) {
			$this->assertTrue( $this->filesystem->exists( $file ) );
		}

		do_action( 'update_option_wp_rocket_settings', $input['settings'], $input['old_settings'] );

		$rucss_usedcss_query = $container->get( 'rucss_used_css_query' );
		$resultAfterTruncate = $rucss_usedcss_query->query();

		if ( $this->input['remove_unused_css']
				&&
				isset( $input['settings']['remove_unused_css_safelist'], $input['old_settings']['remove_unused_css_safelist'] )
				&&
				$input['settings']['remove_unused_css_safelist'] !== $input['old_settings']['remove_unused_css_safelist']
		 ) {
			$this->assertCount( 0, $resultAfterTruncate );

			// Test that cache Files are deleted.
			$this->checkEntriesDeleted( $input['cache_files'] );
		} else {
			$this->assertCount( count( $input['items'] ), $result );

			// Test that cache Files are still available.
			foreach ( $input['cache_files'] as $file => $content ) {
				$this->assertTrue( $this->filesystem->exists( $file ) );
			}
		}
	}

	public function set_rucss_option() {
		return $this->input['remove_unused_css'] ?? false;
	}
}
