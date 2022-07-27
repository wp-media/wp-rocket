<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\CDN\Cloudflare;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare::display_server_pushing_mode_notice
 *
 * @group  ThirdParty
 */
class Test_DisplayServerPushingModeNotice extends TestCase{
    private $options;
    private $cloudflare;

	public function setUp(): void {
		parent::setUp();

		$this->options  = Mockery::mock( Options_Data::class );
        $this->cloudflare = new Cloudflare( $this->options );

		$this->stubTranslationFunctions();
	}

    /**
	 * @dataProvider configTestData
	 */
    public function testShouldReturnExpected( $config, $expected ) {
		
		Functions\when( 'get_current_screen' )->justReturn( $config['current_screen'] );
		Functions\when( 'current_user_can' )->justReturn( $config['capability'] );
        Functions\expect('rocket_get_constant')->with('CLOUDFLARE_HTTP2_SERVER_PUSH_ACTIVE', false)->andReturn($config['server_push']);

		$this->options->shouldReceive( 'get' )
			->with( 'remove_unused_css', 0 )
			->andReturn( $config['remove_unused_css'] );

        $this->options->shouldReceive( 'get' )
			->with( 'minify_concatenate_css', 0 )
			->andReturn( $config['minify_concatenate_css'] );

		if ( $expected ) {
			Functions\expect( 'rocket_notice_html' )
				->once();
		} else {
			Functions\expect( 'rocket_notice_html' )->never();
		}

		$this->cloudflare->display_server_pushing_mode_notice();
	}
}
