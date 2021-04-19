<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\CapTrait;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber::clean_used_css_directory_cpcss_disabled
 *
 * @group  RUCSS
 * @group  AdminOnly
 */
class Test_CleanUsedCssDirectoryCpcssDisabled extends FilesystemTestCase {
	use CapTrait;

	private        $rucss_option;
	private static $admin_user_id;
	private static $contributer_user_id;

	protected $path_to_test_data = '/inc/Engine/Optimization/RUCSS/Admin/Subscriber/cleanUsedCssdirectoryCpcssDisabled.php';

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		CapTrait::hasAdminCapBeforeClass();
		CapTrait::setAdminCap();

		self::$admin_user_id = static::factory()->user->create( [ 'role' => 'administrator' ] );
		self::$contributer_user_id = static::factory()->user->create( [ 'role' => 'contributor' ] );
	}

	public function tearDown() : void {

		parent::tearDown();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $input, $expected ){
		if ( isset( $input['cap'] ) ) {
			if ( $input['cap'] ) {
				$user_id = self::$admin_user_id;
			}else{
				$user_id = self::$contributer_user_id;
			}
			wp_set_current_user( $user_id );
		}

		$this->rucss_option = $input['remove_unused_css'] ?? false;
		add_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );

		// Test that used_css Files are available.
		foreach ( $input['files'] as $file => $content ) {
			$this->assertTrue( $this->filesystem->exists( $file ) );
		}

		do_action( 'update_option_wp_rocket_settings', $input['old_value'], $input['new_value'] );

		// Test that used_css Files are available.
		foreach ( $expected['files'] as $file => $content ) {
			if ( $expected['cleaned'] ) {
				$this->assertFalse( $this->filesystem->exists( $file ) );
			}else{
				$this->assertTrue( $this->filesystem->exists( $file ) );
			}
		}


	}

	public function set_rucss_option() {
		return $this->rucss_option;
	}
}
