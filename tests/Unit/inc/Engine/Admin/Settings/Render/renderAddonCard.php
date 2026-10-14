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
 * @covers \WP_Rocket\Engine\Admin\Settings\Render::get_dashboard_sidebar
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
					'image'        => [ 'src' => 'https://example.org/img.png', 'alt' => 'alt' ],
					'lead'         => 'Lead',
					'text'         => 'Text',
					'list'         => [ '<strong>One</strong> thing', 'Two' ],
					'button_label' => 'Install Imagify',
					'button_url'   => 'https://example.org/install',
					'is_external'  => false,
				],
				[
					'id'           => 'rocketcdn',
					'title'        => 'Deliver your content faster',
					'image'        => [ 'src' => '', 'alt' => '' ],
					'lead'         => 'Propel',
					'text'         => 'from 100+ edge locations',
					'list'         => [ 'Faster' ],
					'button_label' => 'Upgrade Now',
					'button_url'   => '#page_cdn',
					'is_external'  => false,
				],
			]
		);

		$render = new Render( dirname( __DIR__, 7 ) . '/views/settings', Mockery::mock( PluginFamily::class ) );

		$this->html = $render->get_dashboard_sidebar();
	}

	public function testShouldRenderTheCategoryTitleOnce() {
		$this->assertSame( 1, substr_count( $this->html, 'Add-ons' ) );
		$this->assertSame( 2, substr_count( $this->html, 'class="wpr-addonCard wpr-addonCard--' ) );
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
		$this->assertStringContainsString( 'href="#page_cdn">Upgrade Now</a>', $this->html );
		$this->assertStringContainsString( 'href="https://example.org/install">Install Imagify</a>', $this->html );
		$this->assertStringNotContainsString( 'target="_blank"', $this->html );
		$this->assertStringNotContainsString( 'data-micromodal', $this->html );
	}

	public function testShouldOnlyRenderTheImageWhenThereIsOne() {
		$this->assertSame( 1, substr_count( $this->html, '<img class="wpr-addonCard-image"' ) );
	}
}
