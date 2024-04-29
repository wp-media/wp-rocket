<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\Lazyload\CSS\Admin\Subscriber;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\Lazyload\CSS\Admin\Subscriber::maybe_add_error_notice
 * @group AdminOnly
 */
class Test_maybeAddErrorNotice extends FilesystemTestCase {

	protected $path_to_test_data = '/inc/Engine/Media/Lazyload/CSS/Admin/Subscriber/maybeAddErrorNoticeIntegration.php';

	private static $user_id;


	public static function set_up_before_class()
	{
		parent::set_up_before_class();
		self::$user_id = static::factory()->user->create( [ 'role' => 'administrator' ] );
	}

	public static function tear_down_after_class() {
		set_current_screen( 'front' );
	}

	/**
     * @dataProvider providerTestData
     */
    public function testShouldDoAsExpected( $config, $expected )
    {
		set_current_screen( 'settings_page_wprocket' );

		if($config['can']) {
			wp_set_current_user( self::$user_id );
		}

		if ( ! $config['writable'] ) {
			$this->filesystem->chmod( '/wp-content/cache/background-css', 0444 );
		}


		ob_start();
		do_action('admin_notices');
		$actual = ob_get_clean();

		if ( ! empty( $actual ) ) {
			$actual = $this->format_the_html( $actual );
		}

		if($expected['contains']) {
			$this->assertStringContainsString(
				$this->format_the_html( $expected['content'] ),
				$this->format_the_html( $actual )
			);
		} else {
			$this->assertStringNotContainsString(
				$this->format_the_html( $expected['content'] ),
				$this->format_the_html( $actual )
			);
    	}
	}
}
