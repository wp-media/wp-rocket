<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Recommendations\SettingsSubscriber;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\SettingsSubscriber;

/**
 * Test class for SettingsSubscriber integration with WordPress hooks
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class MaybeFetchAfterSettingsChangeTest extends TestCase {

	public function set_up() {
		parent::set_up();
		
		// Force admin context to ensure admin/options.php loads.
		if ( ! defined( 'WP_ADMIN' ) ) {
			define( 'WP_ADMIN', true );
		}
		
		// Load admin options file to register the rocket_after_save_options hook.
		require_once WP_ROCKET_ADMIN_PATH . 'options.php';
	}

	public function tear_down() {
		parent::tear_down();
	}

	/**
	 * Test that the rocket_after_save_options hook exists and fires when options are saved.
	 */
	public function testShouldFireRocketAfterSaveOptionsHook() {
		$hook_fired = false;
		$old_value_received = null;
		$new_value_received = null;

		// Hook into the action to verify it fires.
		add_action(
			'rocket_after_save_options',
			function( $old_value, $new_value ) use ( &$hook_fired, &$old_value_received, &$new_value_received ) {
				$hook_fired = true;
				$old_value_received = $old_value;
				$new_value_received = $new_value;
			},
			10,
			2
		);

		$old_options = [ 'minify_css' => 0 ];
		$new_options = [ 'minify_css' => 1 ];

		// Set up initial options.
		update_option( WP_ROCKET_SLUG, $old_options );

		// Update options to trigger the hook.
		update_option( WP_ROCKET_SLUG, $new_options );

		// Verify hook fired with correct parameters.
		$this->assertTrue( $hook_fired, 'rocket_after_save_options hook should fire when settings are saved' );
		$this->assertSame( $old_options, $old_value_received, 'Old options should be passed to hook' );
		
		// Check the minify_css value we changed - WP Rocket may auto-add minify_css_key
		$this->assertArrayHasKey( 'minify_css', $new_value_received, 'New options should contain minify_css' );
		$this->assertSame( 1, $new_value_received['minify_css'], 'New minify_css value should be 1' );
	}

	/**
	 * Test that the SettingsSubscriber is properly registered in the container.
	 */
	public function testShouldRegisterSettingsSubscriberInContainer() {
		$container = apply_filters( 'rocket_container', null );
		
		$this->assertNotNull( $container, 'Container should exist' );
		$this->assertTrue( $container->has( 'ri_recommendations_settings_subscriber' ), 'SettingsSubscriber should be registered in container' );
		
		if ( $container->has( 'ri_recommendations_settings_subscriber' ) ) {
			$subscriber = $container->get( 'ri_recommendations_settings_subscriber' );
			$this->assertInstanceOf(
				'WP_Rocket\Engine\Admin\RocketInsights\Recommendations\SettingsSubscriber',
				$subscriber,
				'Subscriber should be correct instance'
			);
		}
	}
}
