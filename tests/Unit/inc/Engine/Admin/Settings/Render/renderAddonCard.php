<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Settings\Render;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Dependencies\WPMedia\PluginFamily\Model\PluginFamily;
use WP_Rocket\Engine\Admin\Settings\Render;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Renders the real addon-card.php and category.php templates through Render::get_dashboard_sidebar().
 *
 * Runs in a separate process: the real templates call WordPress functions when included, and PHPUnit
 * replays the included files of this process into the separate-process tests that run later.
 *
 * @covers \WP_Rocket\Engine\Admin\Settings\Render::get_dashboard_sidebar
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 *
 * @group SettingsPage
 */
class Test_RenderAddonCard extends TestCase {
	private $html;

	protected function setUp(): void {
		parent::setUp();

		Functions\stubTranslationFunctions();
		Functions\stubEscapeFunctions();
		Functions\when( 'wp_parse_args' )->alias(
			function ( $args, $defaults = [] ) {
				return array_merge( $defaults, (array) $args );
			}
		);
		Functions\when( 'sanitize_html_class' )->returnArg();
		Functions\when( 'sanitize_key' )->returnArg();
		Functions\when( 'get_current_user_id' )->justReturn( 1 );
		Functions\when( 'get_user_meta' )->justReturn( '' );
		Functions\when( 'esc_url' )->returnArg();
		Functions\when( 'wp_kses' )->returnArg();

		$filesystem = Mockery::mock();
		$filesystem->shouldReceive( 'is_readable' )->andReturn( true );
		Functions\when( 'rocket_direct_filesystem' )->justReturn( $filesystem );

		Filters\expectApplied( 'rocket_dashboard_sidebar_license' )->andReturn( '' );
		Filters\expectApplied( 'rocket_dashboard_sidebar_promos' )->andReturn( '' );
		Filters\expectApplied( 'rocket_dashboard_sidebar_addons' )->andReturn(
			[
				[
					'id'           => 'imagify',
					'title'        => 'Reduce image size by 20%',
					'image'        => [
						'src'    => 'https://example.org/img.png',
						'alt'    => 'alt',
						'width'  => 266,
						'height' => 137,
					],
					'lead'         => 'Lead',
					'text'         => 'Text',
					'list'         => [ '<strong>One</strong> thing', 'Two' ],
					'button_label' => 'Install Imagify',
					'button_url'   => 'https://example.org/install',
					'is_external'  => false,
				],
				[
					'id'                => 'rocketcdn',
					'title'             => 'Deliver your content faster',
					'image'             => [
						'src' => '',
						'alt' => '',
					],
					'lead'              => 'Propel',
					'text'              => 'from 100+ edge locations',
					'list'              => [ 'Faster' ],
					'button_label'      => 'Upgrade Now',
					'button_url'        => '',
					'button_class'      => 'wpr-rocketcdn-open',
					'button_attributes' => [ 'data-micromodal-trigger' => 'wpr-rocketcdn-modal' ],
					'is_external'       => false,
				],
				[
					'id'           => 'rocketcdn-stripe',
					'title'        => 'Stripe',
					'lead'         => 'Lead',
					'text'         => 'Text',
					'button_label' => 'Upgrade Now',
					'button_url'   => 'https://example.org/checkout',
					'button_class' => 'wpr-rocketcdn-open',
					'is_external'  => false,
				],
				[
					'title'        => 'No id',
					'lead'         => 'Lead',
					'text'         => 'Text',
					'button_label' => 'Go',
					'button_url'   => 'https://example.org/go',
					'is_external'  => false,
				],
			]
		);

		$render = new Render( dirname( __DIR__, 7 ) . '/views/settings', Mockery::mock( PluginFamily::class ) );

		$this->html = $render->get_dashboard_sidebar();
	}

	public function testShouldRenderTheCategoryTitleOnce() {
		$this->assertSame( 1, substr_count( $this->html, 'Add-ons' ) );
		$this->assertSame( 4, substr_count( $this->html, 'class="wpr-addonCard wpr-addonCard--' ) );
	}

	public function testShouldRenderOneAccessibleCloseButtonPerCardWithAnId() {
		$this->assertSame( 3, substr_count( $this->html, 'class="wpr-notice-close wpr-icon-close wpr-addonCard-close"' ) );
		$this->assertStringContainsString( '<button type="button" class="wpr-notice-close wpr-icon-close wpr-addonCard-close" data-card-id="imagify"><span class="screen-reader-text">Dismiss this notice</span></button>', $this->html );
		$this->assertStringContainsString( 'data-card-id="rocketcdn"', $this->html );
		$this->assertStringContainsString( 'data-card-id="rocketcdn-stripe"', $this->html );
	}

	public function testShouldRenderAccessibleToggleButtons() {
		$this->assertSame( 2, substr_count( $this->html, '<button type="button" class="wpr-addonCard-toggle" aria-expanded="false"' ) );
		$this->assertStringContainsString( 'aria-controls="wpr-addonCard-list-imagify"', $this->html );
		$this->assertStringContainsString( 'id="wpr-addonCard-list-imagify"', $this->html );
		$this->assertStringContainsString( 'aria-controls="wpr-addonCard-list-rocketcdn"', $this->html );
		$this->assertStringContainsString( 'data-label-less="Show less"', $this->html );
		$this->assertStringContainsString( 'data-label-more="Learn more"', $this->html );
		// The list is collapsed until the toggle is used.
		$this->assertSame( 2, substr_count( $this->html, '" hidden>' ) );
	}

	public function testShouldRenderTheButtonsAsPlainLinks() {
		$this->assertStringContainsString( '<a class="wpr-button wpr-addonCard-button" href="https://example.org/install">Install Imagify</a>', $this->html );
		$this->assertStringNotContainsString( 'target="_blank"', $this->html );
	}

	public function testShouldRenderTheLinkWithTheExtraClass() {
		$this->assertStringContainsString( '<a class="wpr-button wpr-addonCard-button wpr-rocketcdn-open" href="https://example.org/checkout">Upgrade Now</a>', $this->html );
	}

	public function testShouldRenderAButtonWithTheModalTriggerWhenThereIsNoUrl() {
		$this->assertStringContainsString( '<button type="button" class="wpr-button wpr-addonCard-button wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal">Upgrade Now</button>', $this->html );
		$this->assertSame( 1, substr_count( $this->html, 'data-micromodal-trigger' ) );
	}

	public function testShouldOnlyRenderTheImageWhenThereIsOne() {
		$this->assertSame( 1, substr_count( $this->html, '<img class="wpr-addonCard-image"' ) );
		$this->assertStringContainsString( 'alt="alt" width="266" height="137">', $this->html );
	}
}
