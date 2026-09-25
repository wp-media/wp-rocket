<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use WPMedia\PHPUnit\Unit\TestCase;

/**
 * Test class covering ::rocket_get_vendor_class_relative_path
 *
 * @uses  ::rocket_get_vendor_autoload_fallback_map
 *
 * @group Functions
 * @group Autoload
 */
class Test_RocketGetVendorClassRelativePath extends TestCase {

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/functions/autoload-fallback.php';
	}

	/**
	 * @dataProvider addDataProvider
	 */
	public function testShouldReturnExpectedPath( $class_name, $expected ) {
		$this->assertSame( $expected, rocket_get_vendor_class_relative_path( $class_name ) );
	}

	/**
	 * Class names to resolve, with the path expected in return.
	 *
	 * @return array<string,array<int,string>>
	 */
	public function addDataProvider(): array {
		return [
			'should map a mcp-adapter class'           => [
				'WP\MCP\Domain\Tools\McpTool',
				'vendor/wordpress/mcp-adapter/includes/Domain/Tools/McpTool.php',
			],
			'should map a php-mcp-schema class'        => [
				'WP\McpSchema\Server\Tools\DTO\Tool',
				'vendor/wordpress/php-mcp-schema/src/Server/Tools/DTO/Tool.php',
			],
			'should map a class at the prefix root'    => [
				'WP\MCP\Autoloader',
				'vendor/wordpress/mcp-adapter/includes/Autoloader.php',
			],
			'should ignore a WP Rocket class'          => [
				'WP_Rocket\Engine\License\ServiceProvider',
				'',
			],
			'should ignore a Mozart prefixed class'    => [
				'WP_Rocket\Dependencies\League\Container\Container',
				'',
			],
			'should ignore a namespace merely sharing the prefix start' => [
				'WP\MCPSomethingElse\Thing',
				'',
			],
			'should ignore a class outside any prefix' => [
				'WP_Query',
				'',
			],
			'should ignore an empty class name'        => [
				'',
				'',
			],
		];
	}
}
