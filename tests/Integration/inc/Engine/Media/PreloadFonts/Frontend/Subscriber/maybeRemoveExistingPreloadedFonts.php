<?php


namespace WP_Rocket\Tests\Integration\inc\Engine\Media\PreloadFonts\Frontend\Subscriber;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\PreloadFonts\Frontend\Subscriber::maybe_remove_existing_preloaded_fonts
 *
 * @group PerformanceHints
 * @group PreloadFonts
 */
class Test_MaybeRemoveExistingPreloadedFonts extends TestCase
{
	use DBTrait;

	protected $config;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::installPreloadFontsTable();
	}

	public static function tear_down_after_class() {
		self::uninstallPreloadFontsTable();

		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'rocket_buffer', 'maybe_remove_existing_preloaded_fonts' );
		add_filter( 'rocket_remove_existing_preloaded_fonts', [ $this, 'remove_existing_preloaded_fonts' ] );
		add_filter( 'pre_get_rocket_option_auto_preload_fonts', '__return_true' );
	}

	public function tear_down() {
		parent::tear_down();

		$this->restoreWpHook('rocket_buffer');
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected)
	{
		$this->config = $config;
		if ( !empty( $this->config['row'] ) ) {
			$this->addPreloadFonts($this->config['row']);
		}
		$this->assertSame(
			$expected['html'],
			wpm_apply_filters_typed( 'string', 'rocket_buffer', $config['html'] )
		);
	}

	public function remove_existing_preloaded_fonts() {
		return $this->config['remove_existing_preloaded_fonts'];
	}
}
