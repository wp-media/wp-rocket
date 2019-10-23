<?php
namespace WP_Rocket\Tests\Unit\ThirdParty\Plugins\Smush;

use WP_Rocket\Subscriber\Third_Party\Plugins\Smush_Subscriber;
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
    }

    /**
     * Test should disable WP Rocket lazy load functionality when Smush lazyload is enabled
     *
     * @since 3.4.2
     * @author Soponar Cristina
     *
     */
    public function testShouldDisableWPRocketLazyLoad()
    {
        $subscriber  = new Smush_Subscriber();

        Functions\expect( 'get_option' )
            ->once() // called once
            ->andReturn( [ 'lazy_load' => true ] );

        Functions\expect( 'is_plugin_active' )
            ->once()
            ->andReturn( true );

        Functions\when( '__' )
            ->justReturn( 'Smush, ' );

        $this->assertNotEmpty( $subscriber->is_smush_lazyload_active('') );
    }

    /**
      * Test should not disable WP Rocket lazy load functionality when Smush lazyload is disabled
     *
     * @since 3.4.2
     * @author Soponar Cristina
     *
     */
    public function testShouldNotDisableWPRocketLazyLoad()
    {
        $subscriber  = new Smush_Subscriber();

        Functions\expect( 'get_option' )
            ->once() // called once
            ->andReturn( [ ] );

        $this->assertEmpty( $subscriber->is_smush_lazyload_active('') );
    }

    /**
     * Test should not disable WP Rocket lazy load functionality when Smush lazyload is disabled
     *
     * @since 3.4.2
     * @author Soponar Cristina
     *co
     */
    public function testShouldNotMaybeDeactivateLazyload()
    {
        $subscriber  = new Smush_Subscriber();

        Functions\expect( 'get_option' )
            ->once() // called once
            ->andReturn();

        $this->assertEmpty( $subscriber->smush_maybe_deactivate_lazyload() );
    }
}
