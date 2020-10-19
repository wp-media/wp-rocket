<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\License\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\License\Subscriber::add_localize_script_data
 *
 * @group License
 * @group AdminOnly
 */
class AddLocalizeScriptData extends TestCase {
	private static $user;
	private static $pricing;
	private $original_user;
	private $original_pricing;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		$container     = apply_filters( 'rocket_container', null );
		self::$user    = $container->get( 'user' );
		self::$pricing = $container->get( 'pricing' );
	}

	public function setUp() {
		parent::setUp();

		$this->original_user    = $this->getNonPublicPropertyValue( 'user', self::$user, self::$user );
		$this->original_pricing = $this->getNonPublicPropertyValue( 'pricing', self::$pricing, self::$pricing );
	}

	public function tearDown() {
		$this->set_reflective_property( $this->original_user, 'user', self::$user );
		$this->set_reflective_property( $this->original_pricing, 'pricing', self::$pricing );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $data, $expected ) {
		$this->set_reflective_property( $config['user'], 'user', self::$user );
		$this->set_reflective_property( $config['pricing'], 'pricing', self::$pricing );

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_localize_admin_script', $data )
		);
	}
}
