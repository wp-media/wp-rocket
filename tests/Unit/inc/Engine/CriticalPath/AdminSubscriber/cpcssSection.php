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

		Functions\when( 'wp_sprintf_l' )->alias(
			function( $pattern, $args ) {
				return $this->wp_sprintf_l( $pattern, $args );
			}
		);
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

		$this->setUpGenerate( 'container', $expected['data'] );

		ob_start();
		$this->subscriber->cpcss_section();
		ob_get_clean();
	}
}
