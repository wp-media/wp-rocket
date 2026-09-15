<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PluginResolver;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Fixtures\classes\PluginResolverActivePlugin;
use WP_Rocket\Tests\Fixtures\classes\PluginResolverInactivePlugin;
use WP_Rocket\Tests\Fixtures\classes\PluginResolverGatedIds;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\PluginResolver;
use WP_Rocket\ThirdParty\Plugins\SubscriberFactory;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\PluginResolver::get_active_plugins
 *
 * @group  Plugins
 * @group  ThirdParty
 */
class Test_GetActivePlugins extends TestCase {
	/**
	 * PluginResolverGatedIds::IDS minus 'translatepress' and 'the_seo_framework' for
	 * this Unit suite only.
	 *
	 * tests/Unit/bootstrap.php unconditionally preloads the TRP_Translate_Press
	 * fixture class and the TheSEOFramework fixtures.php (a real global
	 * the_seo_framework() function returning a Sitemap stub with a truthy
	 * $loaded) for every Unit test (needed by the pre-existing TranslatePress
	 * and addTsfSitemapToPreload.php callback tests), unlike the Integration
	 * suite, which only loads TRP_Translate_Press under `--group TranslatePress`.
	 * So in this suite specifically, TranslatePress::is_activated() and
	 * TheSEOFramework::is_activated() always report true regardless of the
	 * "target plugin absent" assumption the rest of the gated-ids list relies on.
	 *
	 * @var array<string>
	 */
	private const UNIT_ENV_ALWAYS_ACTIVE_OVERRIDES = [ 'translatepress', 'the_seo_framework' ];

	/**
	 * Resets memoization before each test.
	 *
	 * @inheritDoc
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->reset_memoization();

		// ThirstyAffiliates::is_activated() calls is_plugin_active() directly; stub it
		// absent by default to match every other gated id's "target plugin not
		// installed" assumption in this test environment.
		Functions\when( 'is_plugin_active' )->justReturn( false );
	}

	/**
	 * Resets memoization after each test.
	 *
	 * @inheritDoc
	 */
	protected function tearDown(): void {
		$this->reset_memoization();

		parent::tearDown();
	}

	/**
	 * Phase 0: no registry class implements PluginCompatibilityInterface yet,
	 * so every id defaults active — the registry's full id set is unchanged.
	 * Issue #8789 slices 1-2 opt a growing set of ids into real detection; none
	 * of their target plugins are present in this test environment, so those
	 * ids are excluded from the resolved active set while the rest still
	 * default active.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $expected Expected full registry ids.
	 */
	public function testShouldReturnAllRegistryIdsByDefault( $expected ) {
		$registry = ( new SubscriberFactory() )->get_registry();

		$this->assertSame( $expected, array_keys( $registry ) );

		$expected_active_ids = array_values(
			array_diff(
				array_keys( $registry ),
				array_diff( PluginResolverGatedIds::IDS, self::UNIT_ENV_ALWAYS_ACTIVE_OVERRIDES )
			)
		);

		$this->assertSame( $expected_active_ids, PluginResolver::get_active_plugins( true ) );
	}

	/**
	 * A registry entry implementing PluginCompatibilityInterface::is_activated() as false is excluded.
	 */
	public function testShouldExcludeInactiveInterfaceImplementingClass() {
		$result = $this->get_reflective_method( 'filter_active_registry', PluginResolver::class )->invoke(
			null,
			[ 'inactive_stub' => PluginResolverInactivePlugin::class ]
		);

		$this->assertSame( [], $result );
	}

	/**
	 * A registry entry implementing PluginCompatibilityInterface::is_activated() as true is included.
	 */
	public function testShouldIncludeActiveInterfaceImplementingClass() {
		$result = $this->get_reflective_method( 'filter_active_registry', PluginResolver::class )->invoke(
			null,
			[ 'active_stub' => PluginResolverActivePlugin::class ]
		);

		$this->assertSame( [ 'active_stub' ], $result );
	}

	/**
	 * $force bypasses the memoized result.
	 */
	public function testShouldBypassMemoizationWhenForced() {
		$this->set_reflective_property( [ 'stale_id' ], 'active_plugins', PluginResolver::class );

		$this->assertSame( [ 'stale_id' ], PluginResolver::get_active_plugins() );

		$registry            = ( new SubscriberFactory() )->get_registry();
		$expected_active_ids = array_values(
			array_diff(
				array_keys( $registry ),
				array_diff( PluginResolverGatedIds::IDS, self::UNIT_ENV_ALWAYS_ACTIVE_OVERRIDES )
			)
		);

		$this->assertSame( $expected_active_ids, PluginResolver::get_active_plugins( true ) );
	}

	/**
	 * Resets the memoized active plugins list between tests.
	 *
	 * @return void
	 */
	private function reset_memoization(): void {
		$this->set_reflective_property( null, 'active_plugins', PluginResolver::class );
	}
}
