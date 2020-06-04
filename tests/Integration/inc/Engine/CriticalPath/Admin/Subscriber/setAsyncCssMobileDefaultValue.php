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

    public function setUp() {
        parent::setUp();

        remove_action( 'wp_rocket_upgrade', 'rocket_new_upgrade' );
    }

    public function tearDown() {
        parent::tearDown();

        add_action( 'wp_rocket_upgrade', 'rocket_new_upgrade' );
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
