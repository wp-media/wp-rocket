<?php
namespace WP_Rocket\Tests\Unit\ThirdParty\Plugins\Smush;

use WP_Rocket\Subscriber\Third_Party\Plugins\Smush_Subscriber;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

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

        Functions\when( '__' )
            ->justReturn( 'Smush' );

        $this->assertContains( 'Smush', $subscriber->is_smush_lazyload_active( [] ) );
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

        $this->assertEmpty( $subscriber->is_smush_lazyload_active( [] ) );
    }

    /**
     * Test should not disable WP Rocket lazy load functionality when Smush lazyload is disabled
     *
     * @since 3.4.2
     * @author Soponar Cristina
     *
     */
    public function testShouldNotMaybeDeactivateLazyload()
    {
        $subscriber  = new Smush_Subscriber();

        Functions\expect( 'get_option' )
            ->once() // called once
            ->andReturn();

        Functions\expect( 'update_rocket_option' )
            ->never();

        $subscriber->maybe_deactivate_rocket_lazyload();

        $this->assertTrue( true ); // Prevent "risky" warning.
    }

    /**
     * Test should disable WP Rocket lazy load functionality when Smush lazyload is enabled
     *
     * @since 3.4.2
     * @author Soponar Cristina
     *
     */
    public function testShouldMaybeDeactivateLazyload()
    {
        $subscriber  = new Smush_Subscriber();

        Functions\expect( 'get_option' )
            ->once() // called once
            ->andReturn( [ 'lazy_load' => true ] );
            
        Functions\expect( 'update_rocket_option' )
            ->once()
            ->with('lazyload', '0');

        $subscriber->maybe_deactivate_rocket_lazyload();
        
        $this->assertTrue( true ); // Prevent "risky" warning.
    }
}
