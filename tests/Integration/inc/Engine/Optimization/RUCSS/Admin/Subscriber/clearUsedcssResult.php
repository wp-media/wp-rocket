<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\integration\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use WP_Rocket\Tests\Integration\CapTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber::clear_usedcss_result
 *
 * @group  RUCSS
 * @group  AdminOnly
 */
class Test_ClearUsedcssResult extends TestCase {

	protected $path_to_test_data = '/inc/Engine/Optimization/RUCSS/Admin/Subscriber/clearUsedcssResult.php';

	private static $admin_user_id = 0;
	private static $contributer_user_id = 0;
	private $enabled;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		$admin_role = get_role( 'administrator' );
		$admin_role->add_cap( 'rocket_remove_unused_css' );

		self::$admin_user_id = static::factory()->user->create( [ 'role' => 'administrator' ] );
		self::$contributer_user_id = static::factory()->user->create( [ 'role' => 'contributor' ] );
	}

	public function tear_down() : void {
		parent::tear_down();

		remove_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss'] );
		delete_transient( 'rocket_clear_usedcss_response' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $input, $expected ) {
		if ( isset( $input['cap'] ) ) {
			if ( $input['cap'] ) {
				$user_id = self::$admin_user_id;
			}else{
				$user_id = self::$contributer_user_id;
			}
			wp_set_current_user( $user_id );
		}

		$this->enabled = $input['enabled'];

		add_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss'] );

		if ( isset( $input['transient'] ) && $input['transient'] ) {
			set_transient( 'rocket_clear_usedcss_response', $input['transient'] );
		}

		set_current_screen( 'settings_page_wprocket' );

		$actual = $this->getActualHtml();

		if ( $expected['show_notice'] ) {
			$this->assertStringContainsString( $this->format_the_html( $expected['notice_html'] ), $actual );
		}else{
			$this->assertStringNotContainsString( $this->format_the_html( $expected['notice_html'] ), $actual );
		}

	}

	private function getActualHtml() {
		ob_start();
		do_action( 'admin_notices' );

		return $this->format_the_html( ob_get_clean() );
	}

	public function set_rucss() {
		return $this->enabled;
	}
}
