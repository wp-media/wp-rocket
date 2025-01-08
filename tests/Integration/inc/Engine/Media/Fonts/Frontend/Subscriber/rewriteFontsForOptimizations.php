<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\Fonts\Frontend\Subscriber;

use WP_Rocket\Tests\HTTPCallTrait;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\Fonts\Frontend\Subscriber::rewrite_fonts
 * @group HostFontsLocally
 */
class Test_RewriteFontsForOptimizations extends FilesystemTestCase {
	use HttpCallTrait;

	protected $path_to_test_data = '/inc/Engine/Media/Fonts/Frontend/Subscriber/rewriteFontsForOptimizations.php';

	protected $config;

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept('rocket_buffer', 'rewrite_fonts', 18);
		add_filter( 'pre_get_rocket_option_host_fonts_locally', [ $this, 'host_fonts_locally' ] );
		add_filter( 'rocket_host_fonts_locally_inline_css', [ $this, 'locally_inline_css' ] );
		add_filter('rocket_exclude_locally_host_fonts', [ $this, 'exclude_locally_host_fonts' ] );
		$this->setup_http();

	}

	public function tear_down() {
		remove_filter('pre_get_rocket_option_host_fonts_locally', [$this, 'host_fonts_locally']);
		remove_filter('rocket_host_fonts_locally_inline_css', [$this, 'locally_inline_css']);
		remove_filter('rocket_exclude_locally_host_fonts', [ $this, 'exclude_locally_host_fonts' ] );

		$this->restoreWpHook('rocket_buffer');
		$this->tear_down_http();


		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		$this->config = $config;

		$this->assertSame(
			$expected['html'],
			wpm_apply_filters_typed('string', 'rocket_buffer', $config['html'])
		);
	}

	public function host_fonts_locally() {
		return $this->config['host_fonts_locally'];
	}

	public function locally_inline_css() {
		return $this->config['locally_inline_css'];
	}

	public function exclude_locally_host_fonts() {
		return $this->config['exclude_locally_host_fonts'] ?? [];
	}
}
