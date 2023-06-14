<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\CDN\Cloudflare;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare::display_server_pushing_mode_notice
 *
 * @group ThirdParty
 * @group CloudflarePlugin
 */
class Test_DisplayServerPushingModeNotice extends TestCase{
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

        $this->options  = Mockery::mock( Options_Data::class );
		$this->option_api = Mockery::mock(Options::class);
		$this->beacon = Mockery::mock(Beacon::class);

        $this->cloudflare = new Cloudflare( $this->options, $this->option_api, $this->beacon );

        $this->stubTranslationFunctions();
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnExpected( $config, $expected ) {

        $this->constants['CLOUDFLARE_PLUGIN_DIR'] = true;
        $this->constants['CLOUDFLARE_HTTP2_SERVER_PUSH_ACTIVE'] = $config['server_push'];

        Functions\stubs([
            'get_current_screen'   => $config['current_screen'],
            'current_user_can'    => $config['capability'],
            'get_current_user_id' => 1,
            'get_user_meta' => $config['boxes'],
        ]);

        $this->options->shouldReceive( 'get' )
            ->with( 'remove_unused_css', false )
            ->andReturn( $config['remove_unused_css'] );

        $this->options->shouldReceive( 'get' )
            ->with( 'minify_concatenate_css', false )
            ->andReturn( $config['minify_concatenate_css'] );

        if ( $expected['return'] ) {
            Functions\expect( 'rocket_notice_html' )
                ->once();
        } else {
            Functions\expect( 'rocket_notice_html' )->never();
        }

        $this->cloudflare->display_server_pushing_mode_notice();
    }
}
