<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\License\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\License\Subscriber::display_upgrade_section
 *
 * @group License
 * @group AdminOnly
 */
class DisplayUpgradeSection extends TestCase {
	private static $user;
	private $original_value;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		$container   = apply_filters( 'rocket_container', null );
		self::$user  = $container->get( 'user' );
	}

	public function setUp() {
		parent::setUp();

		$this->original_value = $this->getNonPublicPropertyValue( 'user', self::$user, self::$user );
	}

	public function tearDown() {
		$this->set_reflective_property( $this->original_value, 'user', self::$user );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDisplayExpected( $data, $expected ) {
		$this->set_reflective_property( $data, 'user', self::$user );

		$this->assertSame(
			$this->format_the_html( $expected ),
			$this->getActualHtml()
		);
	}

	private function getActualHtml() {
		ob_start();
		do_action( 'rocket_dashboard_license_info' );
		$actual = ob_get_clean();

		return empty( $actual )
			? $actual
			: $this->format_the_html( $actual );
	}
}
