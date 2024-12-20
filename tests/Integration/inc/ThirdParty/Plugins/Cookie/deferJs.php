<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Cookie;

use WP_Rocket\Tests\Integration\ContentTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\Cookie\Termly::exclude_termly_defer_and_delay_js
 *
 * @group Termly
 */
class Test_deferJs extends TestCase {


	private $defer_js;
	private $exclude_defer_js;

	public function set_up() {
		parent::set_up();

		set_current_screen( 'front' );
	}

	public function tear_down() {
		// Re-enable ATF optimization.
		remove_filter( 'rocket_above_the_fold_optimization', '__return_false' );

		remove_filter( 'pre_get_rocket_option_defer_all_js', [ $this, 'set_defer_js' ] );
		remove_filter( 'pre_get_rocket_option_exclude_defer_js', [ $this, 'set_exclude_defer_js' ] );
		delete_post_meta( 100, '_rocket_exclude_defer_all_js' );
		delete_transient( 'wpr_dynamic_lists' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $html, $expected ) {
		$this->exclude_defer_js    = $config['options']['exclude_defer_js'];
		$this->defer_js            = $config['options']['defer_all_js'];

		add_filter( 'pre_get_rocket_option_defer_all_js', [ $this, 'set_defer_js' ] );
		add_filter( 'pre_get_rocket_option_exclude_defer_js', [ $this, 'set_exclude_defer_js' ] );

		set_transient( 'wpr_dynamic_lists', $config['exclusions'], HOUR_IN_SECONDS );

		$this->assertSame(
			$expected,
			wpm_apply_filters_typed('string', 'rocket_buffer', $html )
		);
	}

	public function set_defer_js() {
		return $this->defer_js;
	}

	public function set_exclude_defer_js() {
		return $this->exclude_defer_js;
	}
}
