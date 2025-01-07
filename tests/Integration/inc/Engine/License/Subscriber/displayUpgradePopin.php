<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\License\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\Subscriber::display_upgrade_popin
 *
 * @group License
 * @group AdminOnly
 */
class DisplayUpgradePopin extends TestCase {
	private static $user;
	private static $pricing;
	private $original_user;
	private $original_pricing;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		$container     = apply_filters( 'rocket_container', null );
		self::$user    = $container->get( 'user' );
		self::$pricing = $container->get( 'pricing' );
	}

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'rocket_settings_page_footer', 'display_upgrade_popin' );

		$this->original_user    = $this->getNonPublicPropertyValue( 'user', self::$user, self::$user );
		$this->original_pricing = $this->getNonPublicPropertyValue( 'pricing', self::$pricing, self::$pricing );
	}

	public function tear_down() {
		$this->restoreWpHook( 'rocket_settings_page_footer' );
		$this->set_reflective_property( $this->original_user, 'user', self::$user );
		$this->set_reflective_property( $this->original_pricing, 'pricing', self::$pricing );

		parent::tear_down();
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
