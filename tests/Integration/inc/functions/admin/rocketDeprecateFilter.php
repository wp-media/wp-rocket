<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers ::rocket_deprecate_filter
 * @group Functions
 */
class Test_RocketDeprecateFilter extends TestCase {

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

        add_filter( $config['old_hook'], [$this, 'hook_callback']);

        rocket_deprecate_filter( $config['new_hook'], $config['args'], $config['version'], $config['old_hook'] );

		$this->assertSame( $expected, did_filter( $config['new_hook'] ) );
		$this->assertSame( $expected, did_filter( $config['old_hook'] ) );

        remove_filter( $config['old_hook'], [$this, 'hook_callback']);
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, basename( __FILE__, '.php' ) );
	}

    public function hook_callback() {
        return true;
    }

    public function return_false(){
        return false;
    }

    public function return_true(){
        return false;
    }
}
