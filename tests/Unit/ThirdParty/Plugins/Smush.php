<?php
namespace WP_Rocket\Tests\Unit\ThirdParty\Plugins\Smush;

use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

class Smush extends TestCase
{
    /**
     * Setup constants required by Smush plugin & include the smush.php
     *
     * @since 3.4.2
     * @author Soponar Cristina
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        if ( ! defined( 'WP_SMUSH_VERSION' ) ) {
            define( 'WP_SMUSH_VERSION', '3.2.4' );
        }
        if ( ! defined( 'WP_SMUSH_PREFIX' ) ) {
            define( 'WP_SMUSH_PREFIX', 'wp-smush-' );
        }

        require( WP_ROCKET_PLUGIN_ROOT . 'inc/3rd-party/plugins/smush.php' );
    }

    /**
     * Test should disable WP Rocket lazy load functionality when Smush lazyload is enabled
     *
     * @since 3.4.2
     * @author Soponar Cristina
     *
     * @runInSeparateProcess
     */
    public function testShouldDisableWPRocketLazyLoad()
    {
        Functions\expect( 'get_option' )
            ->once() // called once
            ->andReturn( [ 'lazy_load' => true ] );

        Functions\expect( 'is_plugin_active' )
            ->once()
            ->andReturn( true );

        $this->assertTrue( rocket_maybe_disable_lazyload_smush() );
    }

    /**
      * Test should not disable WP Rocket lazy load functionality when Smush lazyload is disabled
     *
     * @since 3.4.2
     * @author Soponar Cristina
     *
     * @runInSeparateProcess
     */
    public function testShouldNotDisableWPRocketLazyLoad()
    {
        Functions\expect( 'get_option' )
            ->once() // called once
            ->andReturn( [ ] );

        $this->assertFalse( rocket_maybe_disable_lazyload_smush() );
    }

    /**
     * Test should not disable WP Rocket lazy load functionality when Smush lazyload is disabled
     *
     * @since 3.4.2
     * @author Soponar Cristina
     *
     * @runInSeparateProcess
     */
    public function testShouldNotActivateSmush()
    {
        Functions\expect( 'get_option' )
            ->once() // called once
            ->andReturn( [ ] );

        $rocket_activate_smush = rocket_activate_smush();

        $this->assertEmpty( $rocket_activate_smush );
    }

    /**
     * Test should not disable WP Rocket lazy load functionality when Smush lazyload is disabled
     *
     * @since 3.4.2
     * @author Soponar Cristina
     *
     * @runInSeparateProcess
     */
    public function testShouldNotMaybeDeactivateLazyload()
    {
        Functions\expect( 'get_option' )
            ->once() // called once
            ->andReturn();

        $rocket_smush_maybe_deactivate_lazyload = rocket_smush_maybe_deactivate_lazyload();

        $this->assertEmpty( $rocket_smush_maybe_deactivate_lazyload );
    }
}
