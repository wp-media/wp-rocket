<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\Fonts\Frontend\Subscriber;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\Fonts\Frontend\Subscriber::rewrite_fonts
 * @group HostFontsLocally
 */
class Test_RewriteFonts extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Media/Fonts/Frontend/Subscriber/rewriteFonts.php';

	protected $config;

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept('rocket_buffer', 'rewrite_fonts', 18);
		add_filter( 'pre_get_rocket_option_host_fonts_locally', [ $this, 'host_fonts_locally' ] );
		add_filter( 'rocket_host_fonts_locally_inline_css', [ $this, 'locally_inline_css' ] );
	}

	public function tear_down() {
		remove_filter('pre_get_rocket_option_host_fonts_locally', [$this, 'host_fonts_locally']);
		remove_filter('rocket_host_fonts_locally_inline_css', [$this, 'locally_inline_css']);
		$this->restoreWpHook('rocket_buffer');

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		$this->config = $config;

		foreach ($config['css_files'] as $path => $file) {
			rocket_mkdir_p(dirname($path), $this->filesystem);
			$this->filesystem->put_contents($path, $file);
		}

		$this->assertSame(
			$expected['html'],
			apply_filters('rocket_buffer', $config['html'])
		);
	}

	public function host_fonts_locally() {
		return $this->config['host_fonts_locally'];
	}

	public function locally_inline_css() {
		return $this->config['locally_inline_css'];
	}
}
