<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Settings\Page;

use Brain\Monkey\Functions;
use Mockery;
use ReflectionClass;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Admin\Database\Optimization;
use WP_Rocket\Engine\Admin\RocketInsights\Context\Context;
use WP_Rocket\Engine\Admin\Settings\{Page, Render, Settings};
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\SiteList;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\Settings\Page::advanced_cache_section
 * and \WP_Rocket\Engine\Admin\Settings\Page::addons_section
 *
 * @group Admin
 * @group SettingsPage
 */
class Test_CacheRulesSection extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldRegisterSectionAsExpected( $config, $expected ) {
		Functions\stubTranslationFunctions();
		Functions\stubEscapeFunctions();

		$options = Mockery::mock( Options_Data::class );
		$options->shouldReceive( 'get' )->andReturnUsing(
			function ( $key, $default = null ) {
				return $default;
			}
		);

		$beacon = Mockery::mock( Beacon::class );
		$beacon->shouldReceive( 'get_suggest' )->andReturn(
			[
				'url' => 'https://example.org/article',
				'id'  => 'beacon-id',
			]
		);

		Functions\when( 'wp_parse_args' )->alias(
			function ( $args, $defaults = [] ) {
				return array_merge( $defaults, $args );
			}
		);
		Functions\when( 'rocket_valid_key' )->justReturn( false );
		Functions\when( 'rocket_get_constant' )->justReturn( 'https://example.org/img/' );

		if ( ! defined( 'WP_ROCKET_ASSETS_IMG_URL' ) ) {
			define( 'WP_ROCKET_ASSETS_IMG_URL', 'https://example.org/img/' );
		}

		$settings = new Settings( $options );

		$page = new Page(
			[],
			$settings,
			Mockery::mock( Render::class ),
			$beacon,
			Mockery::mock( Optimization::class ),
			Mockery::mock( UserClient::class ),
			Mockery::mock( SiteList::class ),
			'vfs://public/wp-content/plugins/wp-rocket/views',
			$options,
			Mockery::mock( Context::class ),
			Mockery::mock( SubscriptionController::class )
		);

		$class = new ReflectionClass( Page::class );

		foreach ( [ 'advanced_cache_section', 'addons_section' ] as $method ) {
			$reflection = $class->getMethod( $method );
			$reflection->setAccessible( true );
			$reflection->invoke( $page );
		}

		$registered = $settings->get_settings()[ $config['page'] ];
		$sections   = array_keys( $registered['sections'] );

		$this->assertSame( $expected['title'], $registered['title'] );
		$this->assertSame( $expected['last_section'], end( $sections ) );

		$user_cache_section = null;

		foreach ( $registered['sections'] as $id => $section ) {
			if ( isset( $section['fields']['cache_logged_user'] ) ) {
				$user_cache_section = $id;
			}
		}

		$this->assertSame( $expected['user_cache_section'], $user_cache_section );
	}
}
