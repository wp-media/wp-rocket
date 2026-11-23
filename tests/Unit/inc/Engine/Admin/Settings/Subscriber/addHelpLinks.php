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
 * Test class covering \WP_Rocket\Engine\Admin\Settings\Subscriber::add_help_links
 *
 * @group Admin
 * @group SettingsPage
 */
class Test_AddHelpLinks extends TestCase {
	/**
	 * Tests that the Support (contact page, chat when registered) and Documentation links are added to the Help group.
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected navigation.
	 *
	 * @dataProvider configTestData
	 */
	public function testShouldAddHelpLinksToNavigation( $config, $expected ) {
		Functions\stubTranslationFunctions();
		Functions\expect( 'rocket_get_external_url' )
			->once()
			->with(
				'contact',
				[
					'utm_source' => 'wp_plugin',
					'utm_medium' => 'wp_rocket',
				]
				)
			->andReturn( 'https://wp-rocket.me/contact/?utm_source=wp_plugin&utm_medium=wp_rocket' );
		Functions\expect( 'get_rocket_documentation_url' )
			->once()
			->andReturn( 'https://docs.wp-rocket.me/' );

		$support = Mockery::mock( Support_Subscriber::class );
		$support->shouldReceive( 'is_chat_registered' )->andReturn( $config['chat_registered'] );

		$subscriber = new Subscriber( Mockery::mock( Page::class ), Mockery::mock( PluginFamily::class ), $support );

		$this->assertSame( $expected, $subscriber->add_help_links( $config['navigation'] ) );
	}
}
