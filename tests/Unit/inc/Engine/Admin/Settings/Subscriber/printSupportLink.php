<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Settings\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Dependencies\WPMedia\PluginFamily\Controller\PluginFamily;
use WP_Rocket\Engine\Admin\Settings\{Page, Subscriber};
use WP_Rocket\Engine\Support\Subscriber as Support_Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\Settings\Subscriber::print_support_link
 *
 * @group Admin
 * @group SettingsPage
 */
class Test_PrintSupportLink extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		Functions\when( 'current_user_can' )->alias(
			function ( $capability ) use ( $config ) {
				return 'rocket_manage_options' === $capability && $config['can_manage'];
			}
		);

		$support = Mockery::mock( Support_Subscriber::class );
		$support->shouldReceive( 'is_chat_registered' )->andReturn( $config['chat_registered'] );

		$page = Mockery::mock( Page::class );

		if ( $expected['printed'] ) {
			Functions\expect( 'rocket_get_external_url' )
				->once()
				->with( 'contact', [ 'utm_source' => 'wp_plugin', 'utm_medium' => 'wp_rocket' ] )
				->andReturn( 'https://wp-rocket.me/contact/?utm_source=wp_plugin&utm_medium=wp_rocket' );
			$page->shouldReceive( 'render_support_launcher' )
				->once()
				->with( 'https://wp-rocket.me/contact/?utm_source=wp_plugin&utm_medium=wp_rocket' );
		} else {
			$page->shouldNotReceive( 'render_support_launcher' );
		}

		( new Subscriber( $page, Mockery::mock( PluginFamily::class ), $support ) )->print_support_link();
	}

	public function testShouldPrintOnTheSettingsPageFooter() {
		$this->assertSame(
			'print_support_link',
			Subscriber::get_subscribed_events()['admin_footer-settings_page_wprocket']
		);
	}
}
