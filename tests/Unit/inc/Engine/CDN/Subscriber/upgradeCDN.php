<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Subscriber;

use Mockery;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\CDN;
use WP_Rocket\Engine\CDN\Subscriber;

/**
 * Test class covering \WP_Rocket\Engine\CDN\Subscriber::on_update_add_cdn_type_option
 * @group  CDN
 */
class Test_UpgradeCDN extends TestCase {
    private $cdn;
	private $options;
	private $subscriber;

    public function setUp() : void {
		parent::setUp();

        $this->cdn        = Mockery::mock( CDN::class );
		$this->options    = Mockery::mock( Options_Data::class );
		$this->subscriber = new Subscriber(
			$this->options,
			$this->cdn
		);
    }


    /**
	 * @dataProvider configTestData
	 */
    public function testShouldSetExpectedCdnType( array $config, array $expected ) {
        $this->options
            ->expects()
            ->get( 'wp_rocket_settings', [] )
            ->andReturn( $config['options'] );

        if ( isset( $config['rocketcdn_user_token'] ) ) {
            $this->options
                ->expects()
                ->get( 'rocketcdn_user_token' )
                ->andReturn( $config['rocketcdn_user_token'] );
        }

        Functions\expect( 'rocket_get_constant' )
            ->with( 'ROCKETCDN_VERSION' )
            ->andReturn( $config['rocketcdn_version'] ?? '' );

        Functions\when( 'rocket_get_constant' )
            ->alias( function( $constant ) use ( $config ) {
                if ( 'ROCKETCDN_VERSION' === $constant ) {
                    return $config['rocketcdn_version'] ?? '';
                }
                return null;
            } );

        if ( $expected['should_update'] ) {
            $this->options
                ->expects()
                ->set( 'wp_rocket_settings', $expected['options'] );
        } else {
            $this->options->shouldNotReceive( 'set' );
        }

        $this->subscriber->on_update_add_cdn_type_option( $config['new_version'], $config['old_version'] );
    }
}