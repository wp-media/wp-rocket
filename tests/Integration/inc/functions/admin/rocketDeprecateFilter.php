<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use WP_Rocket\Tests\Integration\IsolateHookTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers ::rocket_deprecate_filter
 * @group Functions
 */
class Test_RocketDeprecateFilter extends TestCase {

    use IsolateHookTrait;

    public function set_up() {
        parent::set_up();

        add_action( 'deprecated_hook_run', [ $this, 'action_hook_callback' ] );
        $this->unregisterAllCallbacksExcept('deprecated_hook_run', 'action_hook_callback');
	}

    public function tear_down() {
        $this->restoreWpHook('deprecated_hook_run');

        parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {

        global $wp_filters;

        add_filter( $config['old_hook'], [$this, 'filter_hook_callback']);

        rocket_deprecate_filter( $config['new_hook'], $config['args'], $config['version'], $config['old_hook'] );

		$this->assertSame( $expected, $wp_filters[ $config['new_hook'] ] );
		$this->assertSame( $expected,  $wp_filters[ $config['old_hook'] ] );

        remove_filter( $config['old_hook'], [$this, 'filter_hook_callback']);
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, basename( __FILE__, '.php' ) );
	}

    public function action_hook_callback() {
        return;
    }

    public function filter_hook_callback() {
        return true;
    }

    public function return_false(){
        return false;
    }

    public function return_true(){
        return false;
    }
}
