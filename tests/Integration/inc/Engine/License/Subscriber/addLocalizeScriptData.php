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

	public static function set_up_before_class() {
		parent::set_up_before_class();

		$container     = apply_filters( 'rocket_container', null );
		self::$user    = $container->get( 'user' );
		self::$pricing = $container->get( 'pricing' );
	}

	public function set_up() {
		parent::set_up();

		$this->original_user    = $this->getNonPublicPropertyValue( 'user', self::$user, self::$user );
		$this->original_pricing = $this->getNonPublicPropertyValue( 'pricing', self::$pricing, self::$pricing );
	}

	public function tear_down() {
		$this->set_reflective_property( $this->original_user, 'user', self::$user );
		$this->set_reflective_property( $this->original_pricing, 'pricing', self::$pricing );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $data, $expected ) {
		$this->set_reflective_property( $config['user'], 'user', self::$user );
		$this->set_reflective_property( $config['pricing'], 'pricing', self::$pricing );

		$result = apply_filters( 'rocket_localize_admin_script', $data );

		if ( empty( $expected ) ) {
			$this->assertArrayNotHasKey( 'licence_expiration', $result );
			$this->assertArrayNotHasKey( 'promo_end', $result );
		} else {
			foreach ( $expected as $key => $value ) {
				$this->assertArrayHasKey( $key, $result );
				$this->assertContains( $value, $result );
			}
		}
	}
}
