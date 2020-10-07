<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\License\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\License\Subscriber::display_upgrade_popin
 *
 * @group License
 * @group AdminOnly
 */
class DisplayUpgradePopin extends TestCase {
	private static $user;
	private static $pricing;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		$container     = apply_filters( 'rocket_container', null );
		self::$user    = $container->get( 'user' );
		self::$pricing = $container->get( 'pricing' );
	}

	public function setUp() {
		parent::setUp();

		$this->unregisterAllCallbacksExcept( 'rocket_settings_page_footer', 'display_upgrade_popin' );
	}

	public function tearDown() {
		$this->restoreWpFilter( 'rocket_settings_page_footer' );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDisplayExpected( $user_data, $pricing_data, $expected ) {
		$this->set_reflective_property( $user_data, 'user', self::$user );
		$this->set_reflective_property( $pricing_data, 'pricing', self::$pricing );

		$this->assertSame(
			$this->format_the_html( $expected ),
			$this->getActualHtml()
		);
	}

	private function getActualHtml() {
		ob_start();
		do_action( 'rocket_settings_page_footer' );
		$actual = ob_get_clean();

		return empty( $actual )
			? $actual
			: $this->format_the_html( $actual );
	}
}
