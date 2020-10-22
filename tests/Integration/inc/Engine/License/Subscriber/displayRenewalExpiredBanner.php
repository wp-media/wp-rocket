<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\License\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\License\Subscriber::display_renewal_expired_banner
 *
 * @group License
 * @group AdminOnly
 */
class DisplayRenewalExpiredBanner extends TestCase {
	private static $user;
	private static $pricing;
	private $original_user;
	private $original_pricing;
	private static $user_id = 0;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$user_id = $factory->user->create( [ 'role' => 'administrator' ] );
	}

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		$container     = apply_filters( 'rocket_container', null );
		self::$user    = $container->get( 'user' );
		self::$pricing = $container->get( 'pricing' );
	}

	public function setUp() {
		parent::setUp();

		$this->unregisterAllCallbacksExcept( 'rocket_before_dashboard_content', 'display_renewal_expired_banner', 12 );

		wp_set_current_user( self::$user_id );

		$this->original_user    = $this->getNonPublicPropertyValue( 'user', self::$user, self::$user );
		$this->original_pricing = $this->getNonPublicPropertyValue( 'pricing', self::$pricing, self::$pricing );
	}

	public function tearDown() {
		$this->restoreWpFilter( 'rocket_before_dashboard_content' );

		$this->set_reflective_property( $this->original_user, 'user', self::$user );
		$this->set_reflective_property( $this->original_pricing, 'pricing', self::$pricing );

		delete_transient( 'rocket_renewal_banner_' . self::$user_id );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->set_reflective_property( $config['user'], 'user', self::$user );
		$this->set_reflective_property( $config['pricing'], 'pricing', self::$pricing );

		if ( false !== $config['transient'] ) {
			set_transient( 'rocket_renewal_banner_' . self::$user_id, 1, MINUTE_IN_SECONDS );
		}

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
