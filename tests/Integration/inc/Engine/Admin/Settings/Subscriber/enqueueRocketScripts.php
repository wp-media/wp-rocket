<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\Settings\Subscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;

/**
 * @covers \WP_Rocket\Engine\Admin\Settings\Subscriber::enqueue_rocket_scripts
 * @group  AdminOnly
 * @group  SettingsPage
 */
class Test_EnqueueRocketScripts extends AdminTestCase {
    public function set_up() {
        parent::set_up();

        $this->setRoleCap( 'administrator', 'rocket_manage_options' );
    }

    public function tear_down() {
        set_current_screen( 'front' );

        $this->removeRoleCap( 'administrator', 'rocket_manage_options' );

		parent::tear_down();
    }

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldMaybeEnqueueScript( $hook, $expected ) {
        $this->setCurrentUser( 'administrator' );

        set_current_screen( $hook );

        do_action( 'admin_enqueue_scripts', $hook );

        $wp_scripts = wp_scripts();
        $wp_scripts->init();

        if ( $expected ) {
			$this->assertArrayHasKey( 'wistia-e-v1', $wp_scripts->registered );
		} else {
			$this->assertArrayNotHasKey( 'wistia-e-v1', $wp_scripts->registered );
		}
	}

	public function providerTestData() {
		$dir = dirname( __DIR__ ) . '/Page';

		return $this->getTestData( $dir, 'enqueueRocketScripts' );
	}
}
