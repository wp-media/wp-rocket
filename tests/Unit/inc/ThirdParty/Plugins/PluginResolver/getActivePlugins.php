<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PluginResolver;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Fixtures\classes\PluginResolverActivePlugin;
use WP_Rocket\Tests\Fixtures\classes\PluginResolverInactivePlugin;
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
	 * Ids gated by issue #8790 slice 1 that report inactive in this test
	 * environment (none of their target plugins are installed/defined), so
	 * they no longer default-active like the rest of the registry.
	 *
	 * @var array<string>
	 */
	private const SLICE_1_GATED_INACTIVE_IDS = [
		'revolution_slider_subscriber',
		'optimus_webp_subscriber',
		'rapidload',
		'all_in_one_seo_pack',
	];

	/**
	 * Ids gated by issue #8790 slice 2 that report inactive in this test
	 * environment (WPCF7_VERSION undefined; is_plugin_active() stubbed false
	 * in setUp()), so they no longer default-active like the rest of the registry.
	 *
	 * @var array<string>
	 */
	private const SLICE_2_GATED_INACTIVE_IDS = [
		'contactform7',
		'cloudflare_plugin_subscriber',
	];

	/**
	 * Resets memoization before each test.
	 *
	 * @inheritDoc
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->reset_memoization();

		// Cloudflare::is_activated() now calls the real global is_plugin_active();
		// stub it so full-registry iteration doesn't fatal (issue #8790 slice 2).
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
	 * so the registry's full id set is unchanged. Issue #8790 slices 1-2 opt 6
	 * ids into real detection; none of their target plugins are present in
	 * this test environment, so those 6 are excluded from the resolved
	 * active set while the rest still default active.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $registry_ids      Expected full registry ids.
	 * @param array $active_by_default Expected active-by-default ids.
	 */
	public function testShouldReturnAllRegistryIdsByDefault( $registry_ids, $active_by_default ) {
		$registry = ( new SubscriberFactory() )->get_registry();

		$this->assertSame( $registry_ids, array_keys( $registry ) );
		$this->assertSame( $active_by_default, PluginResolver::get_active_plugins( true ) );
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
				array_merge( self::SLICE_1_GATED_INACTIVE_IDS, self::SLICE_2_GATED_INACTIVE_IDS )
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
