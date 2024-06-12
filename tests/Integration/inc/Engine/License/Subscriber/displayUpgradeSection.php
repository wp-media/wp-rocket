<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\License\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\Subscriber::display_upgrade_section
 *
 * @group License
 * @group AdminOnly
 */
class DisplayUpgradeSection extends TestCase {
	private static $user;
	private $original_value;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		$container   = apply_filters( 'rocket_container', null );
		self::$user  = $container->get( 'user' );
	}

	public function set_up() {
		parent::set_up();

		$this->original_value = $this->getNonPublicPropertyValue( 'user', self::$user, self::$user );
	}

	public function tear_down() {
		$this->set_reflective_property( $this->original_value, 'user', self::$user );

		parent::tear_down();
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
