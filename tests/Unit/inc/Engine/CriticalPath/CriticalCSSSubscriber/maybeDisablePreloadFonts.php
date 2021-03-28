<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\CriticalCSSSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber::maybe_disable_preload_fonts
 *
 * @group CriticalPath
 */
class Test_MaybeDisablePreloadFonts extends TestCase {
	use SubscriberTrait;

	public function setUp() : void {
		parent::setUp();

		$this->setUpTests();
	}

    /**
	 * @dataProvider configTestData
	 */
    public function testShouldReturnExpected( $config, $expected ) {
        $this->donotrocketoptimize = $config['DONOTROCKETOPTIMIZE'];

        $this->options->shouldReceive( 'get' )
            ->with( 'async_css', 0 )
            ->andReturn( $config['options']['async_css'] );

        Functions\when( 'is_rocket_post_excluded_option' )->justReturn( $config['is_rocket_post_excluded_option'] );

        $this->critical_css->shouldReceive( 'get_current_page_critical_css' )
            ->atMost()
            ->once()
            ->andReturn( $config['get_current_page_critical_css'] );

        $this->options->shouldReceive( 'get' )
            ->with( 'critical_css', '' )
            ->atMost()
            ->once()
            ->andReturn( $config['options']['critical_css'] );

        $value = $this->subscriber->maybe_disable_preload_fonts();

        if ( $expected ) {
            $this->assertTrue( $value );
        } else {
            $this->assertFalse( $value );
        }
    }
}