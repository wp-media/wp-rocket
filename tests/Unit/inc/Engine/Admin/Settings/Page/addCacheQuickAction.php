<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Settings\Page;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Admin\Database\Optimization;
use WP_Rocket\Engine\Admin\RocketInsights\Context\Context;
use WP_Rocket\Engine\Admin\Settings\{Page, Render, Settings};
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\SiteList;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Admin\Settings\Page::add_cache_quick_action
 *
 * @group SettingsPage
 */
class Test_AddCacheQuickAction extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$page = new Page(
			[
				'slug'       => 'wprocket',
				'title'      => 'WP Rocket',
				'capability' => 'rocket_manage_options',
			],
			Mockery::mock( Settings::class ),
			Mockery::mock( Render::class ),
			Mockery::mock( Beacon::class ),
			Mockery::mock( Optimization::class ),
			Mockery::mock( UserClient::class ),
			Mockery::mock( SiteList::class ),
			'vfs://public/wp-content/plugins/wp-rocket/views',
			Mockery::mock( Options_Data::class ),
			Mockery::mock( Context::class ),
			Mockery::mock( User::class )
		);

		$this->stubTranslationFunctions();
		Functions\expect( 'current_user_can' )->with( 'rocket_purge_cache' )->andReturn( $config['can'] );
		Functions\when( 'rocket_valid_key' )->justReturn( $config['valid_key'] );
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

		$this->assertSame( $expected, $page->add_cache_quick_action( $config['actions'] ) );
	}
}
