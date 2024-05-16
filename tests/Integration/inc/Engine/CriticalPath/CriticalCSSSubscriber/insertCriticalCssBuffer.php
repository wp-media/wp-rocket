<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\CriticalCSSSubscriber;

use WP_Rocket\Tests\Integration\ContentTrait;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber::insert_critical_css_buffer
 *
 * @uses ::rocket_get_constant
 * @uses ::is_rocket_post_excluded_option
 * @uses \WP_Rocket\Engine\CriticalPath\CriticalCss::get_critical_css_content
 * @uses \WP_Rocket\Admin\Options_Data::get
 *
 * @group CriticalPath
 * @group vfs
 */
class Test_InsertCriticalCssBuffer extends FilesystemTestCase {
	use ContentTrait;

	protected $path_to_test_data = '/inc/Engine/CriticalPath/CriticalCSSSubscriber/insertCriticalCssBuffer.php';

	protected static $use_settings_trait = true;
	private static   $user_id;

	private $fallback_css;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$user_id = $factory->user->create(
			[
				'role'          => 'adminstrator',
				'user_nicename' => 'rocket_tester',
			]
		);
	}

	public function set_up() {
		parent::set_up();

		// Disable ATF optimization to prevent DB request (unrelated to the test).
		add_filter( 'rocket_above_the_fold_optimization', '__return_false' );

		add_filter( 'pre_get_rocket_option_async_css', [ $this, 'return_1' ] );
		wp_set_current_user( self::$user_id );
		set_current_screen( 'front' );
	}

	public function tear_down() {
		// Re-enable ATF optimization.
		remove_filter( 'rocket_above_the_fold_optimization', '__return_false' );

		$this->reset_post_types();
		$this->reset_taxonomies();

		remove_filter( 'pre_get_rocket_option_async_css', [ $this, 'return_1' ] );
		remove_filter( 'pre_get_rocket_option_critical_css', [ $this, 'getFallbackCss' ] );
		update_option( 'show_on_front', 'posts' );

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $config, $expected_file, $fallback = null, $js_script = null ) {
		$this->goToContentType( $config );

		if ( isset( $fallback ) && ! empty( $config['fallback_css'] ) ) {
			$this->fallback_css = $config['fallback_css'];
			add_filter( 'pre_get_rocket_option_critical_css', [ $this, 'getFallbackCss' ] );
		}

		$html = apply_filters( 'rocket_buffer', '<html><head><title></title></head><body></body></html>' );

		if ( ! empty( $expected_file ) ) {
			$this->assertGreaterThan( 0, strpos( $html, $this->filesystem->get_contents( $expected_file ) ) );
		}

		if ( isset( $fallback ) && ! empty( $config['fallback_css'] ) ) {
			$this->assertGreaterThan( 0, strpos( $html, $config['fallback_css'] ) );
		}

		if ( isset( $js_script ) && ! empty( $js_script ) ) {
			$this->assertGreaterThan( 0, strpos( $html, $js_script ) );
		}
	}

	public function getFallbackCss() {
		return $this->fallback_css;
	}
}
