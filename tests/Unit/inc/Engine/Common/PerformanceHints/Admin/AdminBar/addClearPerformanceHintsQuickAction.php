<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Common\PerformanceHints\Admin\AdminBar;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Common\PerformanceHints\Admin\AdminBar;
use WP_Rocket\Engine\Media\AboveTheFold\Factory;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Common\PerformanceHints\Admin\AdminBar::add_clear_performance_hints_quick_action
 *
 * @group PerformanceHints
 */
class Test_AddClearPerformanceHintsQuickAction extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$factories = [];

		foreach ( $config['contexts'] as $allowed ) {
			$context = Mockery::mock( ContextInterface::class );
			$context->shouldReceive( 'is_allowed' )->andReturn( $allowed );
			$factory = Mockery::mock( Factory::class );
			$factory->shouldReceive( 'get_context' )->andReturn( $context );
			$factories[] = $factory;
		}

		$admin_bar = new AdminBar( $factories, '' );

		$this->stubTranslationFunctions();
		Functions\when( 'rocket_valid_key' )->justReturn( $config['valid_key'] );
		Functions\when( 'current_user_can' )->justReturn( $config['can'] );
		Functions\when( 'admin_url' )->alias(
			function ( $path ) {
				return "http://example.org/wp-admin/{$path}";
			}
		);
		Functions\when( 'wp_nonce_url' )->alias(
			function ( $url, $action ) {
				return "{$url}|{$action}";
			}
		);

		$this->assertSame( $expected, $admin_bar->add_clear_performance_hints_quick_action( [] ) );
	}
}
