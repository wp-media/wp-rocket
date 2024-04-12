<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers ::rocket_deprecate_action
 * @group Functions
 */
class Test_RocketDeprecateAction extends TestCase {

    public function set_up() {
        add_filter( 'deprecated_hook_trigger_error', [ $this, 'return_false' ] );
	}

    public function tear_down() {
		remove_filter( 'deprecated_hook_trigger_error', [ $this, 'return_true' ] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {

        add_action( $config['old_hook'], [$this, 'hook_callback']);

        rocket_deprecate_action( $config['new_hook'], $config['args'], $config['version'], $config['old_hook'] );

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
