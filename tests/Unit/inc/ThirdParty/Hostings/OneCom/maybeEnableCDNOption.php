<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\OneCom;

use WP_Rocket\ThirdParty\Hostings\OneCom;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\OneCom::maybe_enable_cdn_option
 * @group OneCom
 * @group ThirdParty
 */
class Test_MaybeEnableCDNOption extends TestCase {
    private $onecom;

	public function setUp() : void {
		parent::setUp();

        $this->onecom = new OneCom();
	}

	/**
	 * @dataProvider configTestData
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

		if ( isset( $config['wp_content_dir'] ) ) {
			$this->wp_content_dir = $config['wp_content_dir'];
		}

        $this->assertSame( $expected['return'], $this->onecom->maybe_enable_cdn_option( $config['cdn'] ) );
	}

	public function tearDown(): void {
		parent::tearDown();

		$this->wp_content_dir = 'vfs://public/wp-content';
	}
}
