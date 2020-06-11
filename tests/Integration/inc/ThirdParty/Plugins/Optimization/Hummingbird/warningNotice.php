<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Optimization\Hummingbird;

use WP_Rocket\Tests\Integration\CapTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Optimization\Hummingbird::warning_notice
 *
 * @group Hummingbird
 * @group ThirdParty
 */
class Test_WarningNotice extends TestCase {

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		CapTrait::setAdminCap();

		$user = static::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user );
	}

	public function setUp() {
		parent::setUp();

		set_current_screen( 'settings_page_wprocket' );
		add_filter( 'pre_option_active_plugins', [ $this, 'active_plugin' ] );
	}

	public function tearDown() {
		set_current_screen( 'front' );

		parent::tearDown();

		delete_option( 'wphb_settings' );
		remove_filter( 'pre_option_active_plugins', [ $this, 'active_plugin' ] );
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

	public function addDataProvider() {
		return $this->getTestData( __DIR__, 'settings' );
	}

	/**
	 * @dataProvider addDataProvider
	 */
	public function testShouldDisplayWarningNotice( $hb_settings, $html ) {
		add_filter( 'pre_get_rocket_option_emoji', [ $this, 'return_true' ] );
		add_filter( 'pre_get_rocket_option_minify_css', [ $this, 'return_true' ] );

		update_option( 'wphb_settings', $hb_settings );

		$this->assertContains(
			$this->format_the_html( '<div class="notice notice-error is-dismissible">' . $html . '</div>' ),
			$this->getActualHtml()
		);

		remove_filter( 'pre_get_rocket_option_emoji', [ $this, 'return_true' ] );
		remove_filter( 'pre_get_rocket_option_minify_css', [ $this, 'return_true' ] );
	}
}
