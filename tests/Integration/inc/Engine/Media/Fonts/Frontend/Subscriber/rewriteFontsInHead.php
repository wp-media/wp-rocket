<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\Fonts\Frontend\Subscriber;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\Fonts\Frontend\Subscriber::rewrite_fonts_in_head
 *
 * @group HostFontsLocally
 */
class Test_RewriteFontsInHead extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Media/Fonts/Frontend/Subscriber/rewriteFontsInHead.php';

	protected $config;

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'rocket_head_items', 'rewrite_fonts_in_head', 1000 );
		add_filter( 'pre_get_rocket_option_host_fonts_locally', [ $this, 'host_fonts_locally' ] );
		add_filter( 'pre_get_rocket_option_cdn_type', [ $this, 'cdn_type' ] );
	}

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_host_fonts_locally', [ $this, 'host_fonts_locally' ] );
		remove_filter( 'pre_get_rocket_option_cdn_type', [ $this, 'cdn_type' ] );

		$this->restoreWpHook( 'rocket_head_items' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedItems( $config, $expected ) {
		$this->config = $config;

		$this->assertSame(
			$expected['items'],
			wpm_apply_filters_typed( 'array', 'rocket_head_items', $config['items'] )
		);
		$this->assertSame( $expected['requests'], array_column( $this->get_http_requests(), 'url' ) );
	}

	public function host_fonts_locally() {
		return $this->config['host_fonts_locally'];
	}

	/**
	 * Keeps the RocketCDN licence state from purging the cache while the font CSS is written.
	 */
	public function cdn_type() {
		return 'byocdn';
	}
}
