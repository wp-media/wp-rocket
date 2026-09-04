<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Optimization\Hummingbird;

use WP_Rocket\Tests\Integration\CapTrait;
use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\ThirdParty\Plugins\Optimization\Hummingbird;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\Optimization\Hummingbird::warning_notice
 *
 * @group Hummingbird
 * @group ThirdParty
 */
class Test_WarningNotice extends TestCase {

	/**
	 * @var \WP_Rocket\Event_Management\Event_Manager
	 */
	private $event_manager;

	/**
	 * @var Hummingbird
	 */
	private $hummingbird;

	public static function set_up_before_class() {
		parent::set_up_before_class();
		self::setAdminCap();

		$user = static::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user );
	}

	public function set_up() {
		parent::set_up();

		set_current_screen( 'settings_page_wprocket' );
		add_filter( 'pre_option_active_plugins', [ $this, 'active_plugin' ] );

		// PluginResolver gates hummingbird_subscriber out of the container at boot
		// (is_admin() and is_plugin_active() aren't both true yet when the container
		// boots), so its admin_notices callback was never wired to the event manager.
		// Build the subscriber directly (same approach Test_ExcludeDelayJs uses for
		// Termly, and Test_DisplayServerPushingModeNotice uses for Cloudflare) and
		// wire it here so the notice under test actually fires (issue #8790 slice 3).
		$container           = apply_filters( 'rocket_container', null );
		$this->hummingbird   = new Hummingbird( $container->get( 'options' ) );
		$this->event_manager = $container->get( 'event_manager' );
		$this->event_manager->add_subscriber( $this->hummingbird );
	}

	public function tear_down() {
		$this->event_manager->remove_subscriber( $this->hummingbird );

		set_current_screen( 'front' );

		parent::tear_down();

		delete_option( 'wphb_settings' );
		remove_filter( 'pre_option_active_plugins', [ $this, 'active_plugin' ] );
	}

	private function getActualHtml() {
		ob_start();
		do_action( 'admin_notices' );

		return $this->format_the_html( ob_get_clean() );
	}

	public function active_plugin( $plugins ) {
		if ( ! is_array( $plugins ) ) {
			$plugins = (array) $plugins;
		}

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

		$this->assertStringContainsString(
			$this->format_the_html( '<div class="notice notice-error is-dismissible">' . $html . '</div>' ),
			$this->getActualHtml()
		);

		remove_filter( 'pre_get_rocket_option_emoji', [ $this, 'return_true' ] );
		remove_filter( 'pre_get_rocket_option_minify_css', [ $this, 'return_true' ] );
	}
}
