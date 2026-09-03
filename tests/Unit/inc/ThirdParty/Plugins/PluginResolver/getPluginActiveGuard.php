<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PluginResolver;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Fixtures\classes\PluginResolverActivePlugin;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\PluginResolver;

/**
 * Test class covering the centralized is_plugin_active() availability guard in
 * \WP_Rocket\ThirdParty\Plugins\PluginResolver::filter_active_registry
 *
 * @group  Plugins
 * @group  ThirdParty
 */
class Test_GetPluginActiveGuard extends TestCase {
	/**
	 * When is_plugin_active() is already defined (e.g. on any admin request, or
	 * on the frontend from WP 6.8+), the guard must not attempt to require
	 * wp-admin/includes/plugin.php again. Proven here by defining ABSPATH to a
	 * path with no such file: if the guard incorrectly attempted the require
	 * anyway, this would fatal and fail the test.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldNotRequirePluginFileWhenIsPluginActiveAlreadyDefined() {
		Functions\when( 'is_plugin_active' )->justReturn( false );

		if ( ! defined( 'ABSPATH' ) ) {
			define( 'ABSPATH', '/nonexistent/path/that/does/not/exist/' );
		}

		$result = $this->get_reflective_method( 'filter_active_registry', PluginResolver::class )->invoke(
			null,
			[ 'active_stub' => PluginResolverActivePlugin::class ]
		);

		$this->assertSame( [ 'active_stub' ], $result );
	}
}
