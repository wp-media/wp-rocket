<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DelayJS\Admin\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DelayJS\Admin\Subscriber::sanitize_options
 *
 * @group  DelayJS
 * @group  AdminOnly
 */
class Test_SanitizeOptions extends TestCase {
    private static $admin_settings;

    public static function set_up_before_class() {
        $container = apply_filters( 'rocket_container', null );

        self::$admin_settings = $container->get( 'settings' );
		parent::set_up_before_class();
    }

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'rocket_input_sanitize', 'sanitize_options', 13 );
	}

	public function tear_down() {
		parent::tear_down();

		$this->restoreWpHook( 'rocket_input_sanitize' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
        $result = apply_filters( 'rocket_input_sanitize', $config['input'], self::$admin_settings );

        $this->assertArrayHasKey( 'delay_js', $result );
        $this->assertArrayHasKey( 'delay_js_exclusions', $result );

        $this->assertSame(
			$expected['delay_js'],
			$result['delay_js']
		);

		$this->assertSame(
			array_values( $expected['delay_js_exclusions'] ),
			array_values( $result['delay_js_exclusions'] )
		);
	}
}
