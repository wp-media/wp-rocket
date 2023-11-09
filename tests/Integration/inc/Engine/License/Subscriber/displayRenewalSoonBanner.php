<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\License\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\License\Subscriber::display_renewal_soon_banner
 *
 * @group License
 * @group AdminOnly
 */
class DisplayRenewalSoonBanner extends TestCase {
	private static $user;
	private static $pricing;
	private $original_user;
	private $original_pricing;
	private static $user_id = 0;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$user_id = $factory->user->create( [ 'role' => 'administrator' ] );
	}

	public static function set_up_before_class() {
		parent::set_up_before_class();

		$container     = apply_filters( 'rocket_container', null );
		self::$user    = $container->get( 'user' );
		self::$pricing = $container->get( 'pricing' );
	}

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'rocket_before_dashboard_content', 'display_renewal_soon_banner', 11 );

		wp_set_current_user( self::$user_id );

		$this->original_user    = $this->getNonPublicPropertyValue( 'user', self::$user, self::$user );
		$this->original_pricing = $this->getNonPublicPropertyValue( 'pricing', self::$pricing, self::$pricing );
	}

	public function tear_down() {
		$this->restoreWpHook( 'rocket_before_dashboard_content' );

		$this->set_reflective_property( $this->original_user, 'user', self::$user );
		$this->set_reflective_property( $this->original_pricing, 'pricing', self::$pricing );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->set_reflective_property( $config['user'], 'user', self::$user );
		$this->set_reflective_property( $config['pricing'], 'pricing', self::$pricing );

		$this->assertSame(
			$this->format_the_html($expected ),
			$this->getActualHtml()
		);
	}

	private function getActualHtml() {
		ob_start();
		do_action( 'rocket_before_dashboard_content' );
		$actual = ob_get_clean();

		return empty( $actual )
			? $actual
			: $this->format_the_html( $actual );
	}
}
