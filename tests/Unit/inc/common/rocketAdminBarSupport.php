<?php

namespace WP_Rocket\Tests\Unit\inc\common;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Support\Subscriber;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * Test class covering the Support item of ::rocket_admin_bar
 *
 * @group Common
 * @group AdminBar
 */
class Test_RocketAdminBarSupport extends TestCase {
	protected function setUp(): void {
		parent::setUp();

		if ( ! defined( 'WP_ROCKET_PLUGIN_NAME' ) ) {
			define( 'WP_ROCKET_PLUGIN_NAME', 'WP Rocket' );
		}

		if ( ! defined( 'WP_ROCKET_PLUGIN_SLUG' ) ) {
			define( 'WP_ROCKET_PLUGIN_SLUG', 'wprocket' );
		}

		Functions\stubTranslationFunctions();
		unset( $_SERVER['REQUEST_URI'] );
		Functions\when( 'current_user_can' )->alias(
			function ( $capability ) {
				return 'rocket_manage_options' === $capability;
			}
		);
		Functions\when( 'admin_url' )->returnArg();
		Functions\when( 'get_transient' )->justReturn( false );
		Functions\when( 'get_rocket_documentation_url' )->justReturn( 'https://docs.wp-rocket.me/' );
		Functions\when( 'get_rocket_faq_url' )->justReturn( 'https://docs.wp-rocket.me/faq/' );
		Functions\when( 'rocket_get_external_url' )->alias(
			function ( $target, $args = [] ) {
				return "https://wp-rocket.me/{$target}/?" . http_build_query( $args );
			}
		);

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/common/admin-bar.php';
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldLinkTheSupportItemAccordingToTheAssistant( $config, $expected ) {
		$container = Mockery::mock();

		if ( null === $config['has_assistant'] ) {
			Filters\expectApplied( 'rocket_container' )->once()->with( null )->andReturn( null );
		} else {
			$support = Mockery::mock( Subscriber::class );
			$support->shouldReceive( 'has_assistant' )->once()->andReturn( $config['has_assistant'] );
			$container->shouldReceive( 'get' )->with( 'support_subscriber' )->andReturn( $support );
			Filters\expectApplied( 'rocket_container' )->once()->with( null )->andReturn( $container );
		}

		$items = [];
		$bar   = Mockery::mock();
		$bar->shouldReceive( 'add_menu' )->andReturnUsing(
			function ( $item ) use ( &$items ) {
				$items[ $item['id'] ] = $item;
			}
		);

		rocket_admin_bar( $bar );

		$this->assertSame( $expected['href'], $items['support']['href'] );
		$this->assertSame( 'https://docs.wp-rocket.me/', $items['docs']['href'] );
	}
}
