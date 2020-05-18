<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\AdminSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\AdminSubscriber::cpcss_section
 *
 * @group  CriticalPath
 */
class Test_CpcssSection extends TestCase {
	use GenerateTrait;

	protected static $mockCommonWpFunctionsInSetUp = true;

	protected function setUp() {
		parent::setUp();

		$this->setUpMocks();

		Functions\when( 'esc_js' )->returnArg();
		Functions\when( 'wp_sprintf_l' )->alias(
			function( $pattern, $args ) {
				return $this->wp_sprintf_l( $pattern, $args );
			}
		);
		Functions\when( 'wp_create_nonce' )->justReturn( 'wp_rest_nonce' );
	}

	protected function tearDown() {
		unset( $GLOBALS['post'] );
		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDisplayCPCSSSection( $config, $expected ) {
		$this->setUpTest( $config );

		Functions\when( 'rest_url' )->justReturn( 'http://example.org/wp-rocket/v1/cpcss/post/' . $config['post']->ID );

		$this->setUpGenerate('container', $expected['data'] );

		ob_start();
		$this->subscriber->cpcss_section();
		ob_get_clean();
	}
}
