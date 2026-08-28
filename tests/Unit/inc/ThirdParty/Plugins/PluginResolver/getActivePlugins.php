<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PluginResolver;

use ReflectionMethod;
use ReflectionProperty;
use WP_Rocket\Tests\Fixtures\Classes\PluginResolverActivePlugin;
use WP_Rocket\Tests\Fixtures\Classes\PluginResolverInactivePlugin;
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
	 * Phase 0: no registry class implements PluginCompatibilityInterface yet,
	 * so every id defaults active — the resolved set equals the full registry.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $expected Expected active plugin ids.
	 */
	public function testShouldReturnAllRegistryIdsByDefault( $expected ) {
		$registry = ( new SubscriberFactory() )->get_registry();

		$this->assertSame( $expected, array_keys( $registry ) );
		$this->assertSame( array_keys( $registry ), PluginResolver::get_active_plugins( true ) );
	}

	/**
	 * A registry entry implementing PluginCompatibilityInterface::is_activated() as false is excluded.
	 */
	public function testShouldExcludeInactiveInterfaceImplementingClass() {
		$result = $this->get_filter_active_registry_method()->invoke(
			null,
			[ 'inactive_stub' => PluginResolverInactivePlugin::class ]
		);

		$this->assertSame( [], $result );
	}

	/**
	 * A registry entry implementing PluginCompatibilityInterface::is_activated() as true is included.
	 */
	public function testShouldIncludeActiveInterfaceImplementingClass() {
		$result = $this->get_filter_active_registry_method()->invoke(
			null,
			[ 'active_stub' => PluginResolverActivePlugin::class ]
		);

		$this->assertSame( [ 'active_stub' ], $result );
	}

	/**
	 * $force bypasses the memoized result.
	 */
	public function testShouldBypassMemoizationWhenForced() {
		$property = $this->get_active_plugins_property();
		$property->setValue( null, [ 'stale_id' ] );

		$this->assertSame( [ 'stale_id' ], PluginResolver::get_active_plugins() );

		$registry = ( new SubscriberFactory() )->get_registry();

		$this->assertSame( array_keys( $registry ), PluginResolver::get_active_plugins( true ) );
	}

	/**
	 * Reflection accessor for the private static filter_active_registry() method.
	 *
	 * @return ReflectionMethod
	 */
	private function get_filter_active_registry_method(): ReflectionMethod {
		$method = new ReflectionMethod( PluginResolver::class, 'filter_active_registry' );
		$method->setAccessible( true );

		return $method;
	}

	/**
	 * Reflection accessor for the private static $active_plugins memoization property.
	 *
	 * @return ReflectionProperty
	 */
	private function get_active_plugins_property(): ReflectionProperty {
		$property = new ReflectionProperty( PluginResolver::class, 'active_plugins' );
		$property->setAccessible( true );

		return $property;
	}

	/**
	 * Resets the memoized active plugins list between tests.
	 *
	 * @return void
	 */
	private function reset_memoization(): void {
		$this->get_active_plugins_property()->setValue( null, null );
	}
}
