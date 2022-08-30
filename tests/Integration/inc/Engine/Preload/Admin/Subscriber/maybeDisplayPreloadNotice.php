<?php
namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\Admin\Subscriber;

use WP_Rocket\Engine\Cache\AdminSubscriber;
use WP_Rocket\Tests\Integration\AdminTestCase;

/**
 * @covers \WP_Rocket\Engine\Preload\Admin\Settings::maybe_display_preload_notice
 *
 * @group AdminOnly
 */
class Test_MaybeDisplayPreloadNotice extends AdminTestCase {

	protected $sitemap_preload;

	public function setUp(): void
	{
		parent::setUp();
		add_filter('pre_get_rocket_option_manual_preload', [$this, 'get_sitemap_preload']);
	}

	public function tearDown(): void
	{
		delete_transient('wpr_preload_running');
		remove_filter('pre_get_rocket_option_manual_preload', [$this, 'get_sitemap_preload']);
		parent::tearDown();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		delete_transient('wpr_preload_running');
		$this->sitemap_preload = $config['activated'];
		if ( $config['cap'] ) {
			$this->setRoleCap( 'administrator', 'rocket_manage_options' );
			$this->setCurrentUser( 'administrator' );
		}
		set_current_screen( $config['screen'] );
		if(key_exists('transient', $config) && $config['transient']) {
			set_transient('wpr_preload_running', true);
		}
		ob_start();
		do_action('admin_notices');
		$content = ob_get_clean();
		if($expected['should_contain']) {
			$this->assertStringContainsString($this->format_the_html($expected['html']), $this->format_the_html($content));
		} else {
			$this->assertStringNotContainsString($this->format_the_html($expected['html']), $this->format_the_html($content));
		}
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'maybeDisplayPreloadNotice' );
	}

	public function get_sitemap_preload() {
		return $this->sitemap_preload;
	}
}
