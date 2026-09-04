<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\CDN\Cloudflare;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\ThirdParty\Plugins\CDN\{Cloudflare, CloudflareFacade};

/**
 * Regression test covering \WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare::display_server_pushing_mode_notice
 *
 * Proves the HTTP/2 Server-Push notice is independent of Cloudflare::is_activated()
 * and the private is_plugin_active() credentials check: it fires purely off the
 * CLOUDFLARE_PLUGIN_DIR / CLOUDFLARE_HTTP2_SERVER_PUSH_ACTIVE constants, even when
 * is_plugin_active('cloudflare/cloudflare.php') is mocked false (issue #8790).
 *
 * @group ThirdParty
 * @group CloudflarePlugin
 */
class Test_DisplayServerPushingModeNoticeRegression extends TestCase {
	private $options;
	private $cloudflare;

	/**
	 * @var Options
	 */
	protected $option_api;

	/**
	 * @var Beacon
	 */
	protected $beacon;

	public function setUp(): void {
		parent::setUp();

		$this->options    = Mockery::mock( Options_Data::class );
		$this->option_api = Mockery::mock( Options::class );
		$this->beacon     = Mockery::mock( Beacon::class );

		$this->cloudflare = new Cloudflare( $this->options, $this->option_api, $this->beacon, Mockery::mock( CloudflareFacade::class ) );

		$this->stubTranslationFunctions();
	}

	/**
	 * The notice fires regardless of the plugin's live activation state.
	 */
	public function testShouldStillFireNoticeWhenIsPluginActiveMockedFalse() {
		$this->constants['CLOUDFLARE_PLUGIN_DIR']               = true;
		$this->constants['CLOUDFLARE_HTTP2_SERVER_PUSH_ACTIVE'] = true;

		Functions\stubs(
			[
				'current_user_can'     => true,
				'get_current_user_id'  => 1,
				'get_user_meta'        => [],
			]
		);

		Functions\when( 'get_current_screen' )->justReturn( null );

		// Mocked false: proves the notice does not depend on Cloudflare::is_activated()
		// (which would itself return false here) nor on plugin presence at all.
		Functions\when( 'is_plugin_active' )->justReturn( false );

		$this->options->shouldReceive( 'get' )
			->with( 'remove_unused_css', 0 )
			->andReturn( true );

		Functions\expect( 'rocket_notice_html' )->once();

		$this->cloudflare->display_server_pushing_mode_notice();
	}
}
