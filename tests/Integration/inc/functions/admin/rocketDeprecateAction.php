<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use WP_Rocket\Tests\Integration\IsolateHookTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers ::rocket_do_action_and_deprecated
 * @group Functions
 */
class Test_RocketDeprecateAction extends TestCase {

    use IsolateHookTrait;

    public function set_up() {
        parent::set_up();

        add_action( 'deprecated_hook_run', [ $this, 'hook_callback' ] );
        $this->unregisterAllCallbacksExcept('deprecated_hook_run', 'hook_callback');
	}

    public function tear_down() {
        $this->restoreWpHook('deprecated_hook_run');

        parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {

        add_action( $config['old_hook'], [$this, 'hook_callback']);

        rocket_do_action_and_deprecated( $config['new_hook'], $config['args'], $config['version'], $config['old_hook'] );

		$this->assertSame( $expected, did_action( $config['new_hook'] ) );
		$this->assertSame( $expected, did_action( $config['old_hook'] ) );

        remove_action( $config['old_hook'], [$this, 'hook_callback']);
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, basename( __FILE__, '.php' ) );
	}

    public function hook_callback() {
        return;
    }

    public function return_false(){
        return false;
    }

    public function return_true(){
        return false;
    }
}
