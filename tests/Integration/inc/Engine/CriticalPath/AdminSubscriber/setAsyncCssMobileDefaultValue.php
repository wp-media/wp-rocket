<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\AdminSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\AdminSubscriber::set_async_css_mobile_default_value
 *
 * @group  AdminOnly
 * @group  CriticalPath
 */
class Test_SetAsyncCssMobileDefaultValue extends TestCase {

	/**
	 * @dataProvider configTestData
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
