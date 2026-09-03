<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PluginResolver;

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
	 * Registry ids whose is_activated() reports inactive when their target plugin isn't installed.
	 *
	 * @var array<string>
	 */
	private const GATED_INACTIVE_IDS = [
		'revolution_slider_subscriber',
		'optimus_webp_subscriber',
		'rapidload',
		'all_in_one_seo_pack',
	];

	/**
	 * Resets memoization before each test.
	 *
	 * @inheritDoc
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->reset_memoization();
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
	 * The full registry id set is returned as-is, while the resolved active
	 * set excludes ids whose is_activated() reports inactive in this test
	 * environment.
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
		$expected_active_ids = array_values( array_diff( array_keys( $registry ), self::GATED_INACTIVE_IDS ) );

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
