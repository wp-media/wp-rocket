<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Settings\Render;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Dependencies\WPMedia\PluginFamily\Model\PluginFamily;
use WP_Rocket\Engine\Admin\Settings\Render;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Renders the real support launcher and navigation item templates.
 *
 * Runs in a separate process: the real templates call WordPress functions when included.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 *
 * @group SettingsPage
 */
class Test_RenderSupportViews extends TestCase {
	private $render;

	protected function setUp(): void {
		parent::setUp();

		Functions\stubTranslationFunctions();
		Functions\stubEscapeFunctions();
		Functions\when( 'esc_url' )->returnArg();

		$filesystem = Mockery::mock();
		$filesystem->shouldReceive( 'is_readable' )->andReturn( true );
		Functions\when( 'rocket_direct_filesystem' )->justReturn( $filesystem );

		$this->render = new Render( dirname( __DIR__, 7 ) . '/views/settings', Mockery::mock( PluginFamily::class ) );
	}

	public function testSupportLauncherOpensTheContactPageInANewTab() {
		$html = $this->render->generate( 'support-launcher', [ 'url' => 'https://wp-rocket.me/contact/' ] );

		$this->assertStringContainsString( 'class="wpr-support-launcher"', $html );
		$this->assertStringContainsString( 'href="https://wp-rocket.me/contact/"', $html );
		$this->assertStringContainsString( 'target="_blank"', $html );
		$this->assertStringContainsString( 'rel="noopener noreferrer"', $html );
		$this->assertStringContainsString( 'title="Ask Support"', $html );
		$this->assertStringContainsString( '(opens in a new tab)', $html );
	}

	public function testNavigationItemMarksTheChatOpener() {
		$html = $this->render->generate(
			'navigation-item',
			[
				'id'               => 'support',
				'title'            => 'Support',
				'menu_description' => 'Get help from our team',
				'url'              => 'https://wp-rocket.me/contact/',
				'open_chat'        => true,
			]
		);

		$this->assertStringContainsString( 'href="https://wp-rocket.me/contact/"', $html );
		$this->assertStringContainsString( 'data-wap-open-chat="1"', $html );
		$this->assertStringNotContainsString( 'target=', $html );
	}

	public function testNavigationItemWithoutTheChatIsAPlainLink() {
		$html = $this->render->generate(
			'navigation-item',
			[
				'id'               => 'support',
				'title'            => 'Support',
				'menu_description' => 'Get help from our team',
				'url'              => 'https://wp-rocket.me/contact/',
			]
		);

		$this->assertStringContainsString( 'href="https://wp-rocket.me/contact/"', $html );
		$this->assertStringNotContainsString( 'data-wap-open-chat', $html );
		$this->assertStringNotContainsString( 'target=', $html );
	}
}
