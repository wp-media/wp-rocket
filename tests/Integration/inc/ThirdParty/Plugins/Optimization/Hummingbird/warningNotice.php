<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Optimization\Hummingbird;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers WP_Rocket\inc\ThirdParty\Plugins\Optimization\Hummingbird::warning_notice
 * @group Hummingbird
 * @group ThirdParty
 */
class Test_WarningNotice extends TestCase {
	public static function WpSetupBeforeClass( $factory ) {
		$user = $factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user );
	}

	public function setUp() {
		parent::setUp();

		set_current_screen( 'settings_page_wprocket' );
		add_filter( 'pre_option_active_plugins', [ $this, 'active_plugin' ] );
	}

	public function tearDown() {
		delete_option( 'wphb_settings' );
		remove_filter( 'pre_option_active_plugins', [ $this, 'active_plugin' ] );

		parent::tearDown();
	}

	private function getActualHtml() {
		ob_start();
		do_action( 'admin_notices' );

		return $this->format_the_html( ob_get_clean() );
	}

	public function active_plugin( $plugins ) {
		$plugins[] = 'hummingbird-performance/wp-hummingbird.php';

		return $plugins;
	}

	public function testShouldDisplayWarningNotice() {
		add_filter( 'pre_get_rocket_option_emoji', [ $this, 'return_true' ] );
		add_filter( 'pre_get_rocket_option_minify_css', [ $this, 'return_true' ] );

		update_option( 'wphb_settings', [
			'advanced' => [
				'emoji' => true,
			],
			'page_cache' => [
				'enabled' => true,
			],
			'minify' => [
				'enabled' => true,
			],
		] );

		$this->assertContains(
			$this->format_the_html( '<div class="notice notice-error is-dismissible">
			<p>Please deactivate the following Hummingbird options which conflict with WP Rocket features:</p>
			<ul>
				<li>Hummingbird <em>page caching</em> conflicts with WP Rocket <em>page caching</em></li>
				<li>Hummingbird <em>asset optimization</em> conflicts with WP Rocket <em>file optimization</em></li>
				<li>Hummingbird <em>disable emoji</em> conflicts with WP Rockets <em>disable emoji</em></li>
			</ul></div>' ),
			$this->getActualHtml()
		);

		remove_filter( 'pre_get_rocket_option_emoji', [ $this, 'return_true' ] );
		remove_filter( 'pre_get_rocket_option_minify_css', [ $this, 'return_true' ] );
	}
}
