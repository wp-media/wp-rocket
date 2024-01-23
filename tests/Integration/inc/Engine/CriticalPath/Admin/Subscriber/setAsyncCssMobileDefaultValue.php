<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\Admin\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\Admin\Subscriber::set_async_css_mobile_default_value
 *
 * @group  AdminOnly
 * @group  CriticalPath
 * @group  CriticalPathAdminSubscriber
 */
class Test_SetAsyncCssMobileDefaultValue extends TestCase {
    use ProviderTrait;
	protected static $provider_class = 'Settings';

    public function set_up() {
        parent::set_up();

	    $this->unregisterAllCallbacksExcept( 'wp_rocket_upgrade', 'set_async_css_mobile_default_value', 12 );
    }

    public function tear_down() {

		parent::tear_down();

	    $this->restoreWpHook( 'wp_rocket_upgrade' );
    }

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldUpdateOption( $versions, $update ) {

        do_action( 'wp_rocket_upgrade', $versions['new'], $versions['old'] );

        $options = get_option( 'wp_rocket_settings', [] );

        if ( true === $update ) {
            $this->assertArrayHasKey(
                'async_css_mobile',
                $options
            );

            $this->assertSame(
                0,
                $options['async_css_mobile']
            );
        } else {
            $this->assertArrayNotHasKey(
                'async_css_mobile',
                $options
            );
        }
    }
}
