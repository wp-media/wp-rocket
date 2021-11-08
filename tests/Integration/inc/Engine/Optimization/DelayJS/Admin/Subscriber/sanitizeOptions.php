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

    public static function setUpBeforeClass() : void {
        $container = apply_filters( 'rocket_container', null );

        self::$admin_settings = $container->get( 'settings' );
    }

	public function setUp(): void {
		parent::setUp();

		$this->unregisterAllCallbacksExcept( 'rocket_input_sanitize', 'sanitize_options', 13 );
	}

	public function tearDown() {
		parent::tearDown();

		$this->restoreWpFilter( 'rocket_input_sanitize' );
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

        $this->assertArraySubset(
			$expected['delay_js_exclusions'],
			array_values( $result['delay_js_exclusions'] )
		);
	}
}
