<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\OneCom;

use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\ThirdParty\Hostings\OneCom;
use WPMedia\PHPUnit\Unit\TestCase;
use Brain\Monkey\Functions;
use Mockery;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\OneCom::maybe_disable_cdn_option
 * @group OneCom
 * @group ThirdParty
 */
class Test_MaybeDisableCDNOption extends TestCase {
    private $onecom, $options_api, $option;

	public function setUp() : void {
		parent::setUp();
        
        $this->options_api = Mockery::mock( Options::class );
        $this->options = Mockery::mock( Options_Data::class );

        $this->onecom = new OneCom( $this->options_api, $this->options );
	}

    public function tear_down() {
		parent::tear_down();

		// Reset after each test.
		unset( $_SERVER['ONECOM_DOMAIN_NAME'] );
		unset( $_SERVER['HTTP_HOST'] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {

        Functions\expect( 'rest_sanitize_boolean' )
				->once()
				->andReturn( $config['oc_cdn_enabled'] );

        Functions\when( 'get_option' )
			->alias( function( $value ) use( $config ) {
				if ( 'oc_cdn_enabled' === $value ) {
                    return $config['oc_cdn_enabled'];
                }
			}
		);

        if ( ! $config['oc_cdn_enabled'] && $config['cdn'] ) {

            $domain_name = $_SERVER['ONECOM_DOMAIN_NAME'] = $config['domain'];
            $http_host = $_SERVER['HTTP_HOST'] = $config['domain'];

            Functions\expect( 'wp_unslash' )
                ->times( 4 )
                ->andReturn( $domain_name, $http_host );

            Functions\expect( 'sanitize_text_field' )
                ->times( 4 )
                ->andReturn( $domain_name, $http_host );

            $cdn_url = $this->onecom->build_cname();

            $this->assertSame( $expected['cdn_cname'], $cdn_url );

            $this->options
                ->shouldReceive( 'set' )
                ->with( 'cdn', $config['options']['cdn'] )
                ->andReturn( true );

            $this->options
                ->shouldReceive( 'set' )
                ->with( 'cdn_cnames', $config['options']['cdn_cnames'] )
                ->andReturn( true );

            $this->options
                ->shouldReceive( 'set' )
                ->with( 'cdn_zone', $config['options']['cdn_zones'] )
                ->andReturn( true );

            $this->options
                ->shouldReceive( 'get_options' )
                ->andReturn( [] );

            $this->options_api
                ->shouldReceive( 'set' )
                ->with( 'settings', [] )
                ->andReturn( true );

            Functions\expect( 'rocket_clean_domain' )->once();
        }

        $this->assertSame( $expected['return'], $this->onecom->maybe_disable_cdn_option( $config['cdn'] ) );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'maybeDisableCDNOption' );
	}
}
