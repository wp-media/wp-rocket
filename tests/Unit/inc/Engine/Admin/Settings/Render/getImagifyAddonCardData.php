<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Settings\Render;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Dependencies\WPMedia\PluginFamily\Model\PluginFamily;
use WP_Rocket\Engine\Admin\Settings\Render;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Admin\Settings\Render::get_imagify_addon_card_data
 * @covers \WP_Rocket\Engine\Admin\Settings\Render::get_imagify_benefits
 *
 * @group SettingsPage
 */
class Test_GetImagifyAddonCardData extends TestCase {
	private function getRender( array $partner, bool $white_label = false ) {
		Functions\stubTranslationFunctions();
		Functions\stubEscapeFunctions();
		Functions\when( 'rocket_get_constant' )->alias(
			function ( $name, $default = null ) use ( $white_label ) {
				if ( 'WP_ROCKET_ASSETS_IMG_URL' === $name ) {
					return 'https://example.org/assets/img/';
				}

				return 'WP_ROCKET_WHITE_LABEL_ACCOUNT' === $name ? $white_label : $default;
			}
		);

		$render = Mockery::mock( Render::class, [ 'vfs://views', Mockery::mock( PluginFamily::class ) ] )
			->shouldAllowMockingProtectedMethods()
			->makePartial();

		$render->shouldReceive( 'get_imagify_partner_data' )->andReturn(
			array_merge(
				[
					'activated'   => false,
					'has_api_key' => false,
					'url'         => 'https://example.org/install',
				],
				$partner
			)
		);

		return $render;
	}

	public function testShouldReturnTheInstallCard() {
		$card = $this->getRender( [] )->get_imagify_addon_card_data();

		$this->assertSame( 'imagify', $card['id'] );
		$this->assertSame( 'Reduce image size by 20%', $card['title'] );
		$this->assertSame( 'Give your website an extra speed boost', $card['lead'] );
		$this->assertSame( 'Images can account for 50% of your loading time.', $card['text'] );
		$this->assertSame( 'Install Imagify', $card['button_label'] );
		$this->assertSame( 'https://example.org/install', $card['button_url'] );
		$this->assertFalse( $card['is_external'] );
		$this->assertSame( 'https://example.org/assets/img/wpr-imagify-before-after.png', $card['image']['src'] );
		$this->assertCount( 4, $card['list'] );
	}

	public function testShouldShareTheBenefitsWithTheBanner() {
		$render = $this->getRender( [] );

		$this->assertSame(
			array_column( $render->get_imagify_benefits(), 'text' ),
			$render->get_imagify_addon_card_data()['list']
		);
	}

	public function hiddenCases() {
		return [
			'imagifyActive' => [ [ 'activated' => true ], false ],
			'apiKeySaved'   => [ [ 'has_api_key' => true ], false ],
			'whiteLabel'    => [ [], true ],
		];
	}

	/**
	 * @dataProvider hiddenCases
	 */
	public function testShouldReturnNothing( array $partner, bool $white_label ) {
		$this->assertSame( [], $this->getRender( $partner, $white_label )->get_imagify_addon_card_data() );
	}
}
