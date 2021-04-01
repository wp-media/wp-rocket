<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber::sanitize_options
 *
 * @group  RUCSS
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

		$this->unregisterAllCallbacksExcept( 'rocket_input_sanitize', 'sanitize_options' );
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

        $this->assertArrayHasKey( 'remove_unused_css', $result );
        $this->assertArrayHasKey( 'remove_unused_css_safelist', $result );

        $this->assertSame(
			$expected['remove_unused_css'],
			$result['remove_unused_css']
		);

        $this->assertArraySubset(
			$expected['remove_unused_css_safelist'],
			array_values( $result['remove_unused_css_safelist'] )
		);
	}
}
