<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\FilesystemTestCase;
use WPDieException;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber::truncate_used_css_handler
 *
 * @group  RUCSS
 * @group  AdminOnly
 */
class Test_TruncateUsedCSSHandler extends FilesystemTestCase {
	use DBTrait;

	protected $path_to_test_data = '/inc/Engine/Optimization/RUCSS/Admin/Subscriber/truncateUsedCSSHandler.php';

	private static $admin_user_id = 0;
	private static $contributer_user_id = 0;
	private $rucss_option = false;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::installFresh();

		$admin_role = get_role( 'administrator' );
		$admin_role->add_cap( 'rocket_remove_unused_css' );

		self::$admin_user_id = static::factory()->user->create( [ 'role' => 'administrator' ] );
		self::$contributer_user_id = static::factory()->user->create( [ 'role' => 'contributor' ] );
	}

	public static function tear_down_after_class() {
		parent::tear_down_after_class();

		self::uninstallAll();
	}

	public function tear_down() : void {
		parent::tear_down();

		remove_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );
		unset( $_GET['_wpnonce'] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $input, $expected ) {
		if ( isset( $input['nonce'] ) ) {
			$_GET['_wpnonce'] = wp_create_nonce( $input['nonce'] );
		}

		if ( isset( $input['cap'] ) ) {
			$user_id = $input['cap'] ? self::$admin_user_id : self::$contributer_user_id;
			wp_set_current_user( $user_id );
		}

		if ( isset( $input['option_enabled'] ) ) {
			$this->rucss_option = $input['option_enabled'];
			add_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );
		}

		$container              = apply_filters( 'rocket_container', null );
		$rucss_usedcss_query   = $container->get( 'rucss_used_css_query' );

		foreach ( $input['db_items'] as $item ) {
			$rucss_usedcss_query->add_item( $item );
		}
		$before_count = $rucss_usedcss_query->query( [ 'count' => true ] );

		$this->assertEquals( count( $input['db_items'] ), $before_count );

		foreach ( $input['cache_files'] as $file => $content ) {
			$this->assertTrue( $this->filesystem->exists( $file ) );
		}

		$this->expectException( WPDieException::class );

		do_action( 'admin_post_rocket_clear_usedcss' );

		$rucss_usedcss_query = $container->get( 'rucss_used_css_query' );
		$after_count = $rucss_usedcss_query->query( [ 'count' => true ] );

		if ( $expected['truncated'] ) {
			$this->assertEquals( 0, $after_count );

			// Test that cache Files are still available.
			foreach ( $input['cache_files'] as $file => $content ) {
				$this->assertFalse( $this->filesystem->exists( $file ) );
			}
		}else{
			$this->assertEquals( count( $input['db_items'] ), $after_count );

			// Test that cache Files are still available.
			foreach ( $input['cache_files'] as $file => $content ) {
				$this->assertTrue( $this->filesystem->exists( $file ) );
			}
		}

		if ( ! empty( $expected['notice_details'] ) ) {
			$transient = get_transient( 'rocket_clear_usedcss_response' );
			$this->assertSame( $expected['notice_details'], $transient );
		}

	}

	public function set_rucss_option() {
		return $this->rucss_option;
	}

}
