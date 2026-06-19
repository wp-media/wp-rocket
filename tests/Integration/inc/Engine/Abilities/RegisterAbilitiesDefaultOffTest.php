<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Abilities;

use WP_Rocket\Engine\Abilities\Options\Subscriber as AbilitiesSubscriber;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Integration contract test: verify default-off behaviour of the rocket_enable_abilities gate.
 *
 * The suite-wide bootstrap adds `add_filter( 'rocket_enable_abilities', '__return_true' )` inside
 * `muplugins_loaded`, so every test's baseline has the gate ON (WP's _restore_hooks() preserves it).
 * This test class removes that filter in set_up() to restore the genuine default (false), then
 * re-adds it in tear_down() so subsequent tests stay green.
 *
 * The assertion strategy:
 * - With gate OFF: calling $subscriber->register_get_options_ability() returns early; the inner
 *   GetOptions::register() is never reached. We spy by counting `doing_it_wrong` calls, because
 *   wp_register_ability() outside wp_abilities_api_init fires _doing_it_wrong — so if the call
 *   went through we'd see a count > 0. Gate OFF => count stays 0.
 * - With gate ON: the guard passes, GetOptions::register() is called, wp_register_ability() fires
 *   _doing_it_wrong (registry already initialised, we're not inside the action) => count > 0.
 *
 * @group Abilities
 */
class RegisterAbilitiesDefaultOffTest extends TestCase {
	/**
	 * Whether the test should use the settings trait.
	 *
	 * @var bool
	 */
	protected static $use_settings_trait = false;

	/**
	 * Minimum WordPress version required.
	 */
	private const MIN_WP_VERSION = '6.9';

	/**
	 * Counter for doing_it_wrong calls sourced from wp_register_ability.
	 *
	 * @var int
	 */
	private $doing_it_wrong_count = 0;

	/**
	 * Closure used to hook into doing_it_wrong so it can be removed in tear_down.
	 *
	 * @var callable
	 */
	private $doing_it_wrong_spy;

	/**
	 * Set up the test: remove the suite-wide default-on to restore the genuine default (false).
	 *
	 * @return void
	 */
	public function set_up(): void {
		global $wp_version;

		parent::set_up();

		if ( version_compare( $wp_version, self::MIN_WP_VERSION, '<' ) ) {
			$this->markTestSkipped( 'WordPress Abilities API requires WordPress ' . self::MIN_WP_VERSION . ' or higher.' );
		}

		// Neutralize the suite-wide default-on so the genuine default (false) applies.
		remove_filter( 'rocket_enable_abilities', '__return_true' );

		// Set up spy on _doing_it_wrong / wp_register_ability calls.
		$this->doing_it_wrong_count = 0;
		$this->doing_it_wrong_spy   = function ( string $function_name ) {
			if ( 'wp_register_ability' === $function_name ) {
				++$this->doing_it_wrong_count;
			}
		};
		add_action( 'doing_it_wrong_run', $this->doing_it_wrong_spy, 10, 1 );
	}

	/**
	 * Tear down: restore the suite-wide default-on for subsequent tests.
	 *
	 * @return void
	 */
	public function tear_down(): void {
		remove_action( 'doing_it_wrong_run', $this->doing_it_wrong_spy );

		// Restore the suite-wide default-on so other tests remain green.
		add_filter( 'rocket_enable_abilities', '__return_true' );

		parent::tear_down();
	}

	/**
	 * Test that with the gate at its genuine default (false), the subscriber registers zero abilities.
	 *
	 * @return void
	 */
	public function testGateDefaultOffPreventsAbilityRegistration(): void {
		$container  = apply_filters( 'rocket_container', null );
		$subscriber = $container->get( 'abilities_subscriber' );

		$this->assertInstanceOf( AbilitiesSubscriber::class, $subscriber );

		// Spy count starts at 0; calling register_get_options_ability() with gate OFF
		// must NOT reach wp_register_ability(), so the count stays 0.
		$subscriber->register_get_options_ability();

		$this->assertSame( 0, $this->doing_it_wrong_count, 'With gate OFF, wp_register_ability must not be invoked.' );
	}

	/**
	 * Test that enabling the gate allows ability registration (toggle proof).
	 *
	 * @return void
	 */
	public function testGateEnabledAllowsAbilityRegistration(): void {
		$container  = apply_filters( 'rocket_container', null );
		$subscriber = $container->get( 'abilities_subscriber' );

		$this->assertInstanceOf( AbilitiesSubscriber::class, $subscriber );

		// Enable the gate for this assertion only.
		add_filter( 'rocket_enable_abilities', '__return_true' );

		// wp_register_ability() is called outside its action → fires _doing_it_wrong → count increments.
		$subscriber->register_get_options_ability();

		$this->assertGreaterThan( 0, $this->doing_it_wrong_count, 'With gate ON, wp_register_ability must be invoked.' );

		// tear_down() will re-add the suite-wide filter; remove the local one to avoid duplicates.
		remove_filter( 'rocket_enable_abilities', '__return_true' );
	}
}
