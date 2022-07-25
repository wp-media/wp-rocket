<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\License\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\License\Subscriber::add_license_expire_warning
 *
 * @group License
 * @group AdminOnly
 */
class Test_AddLicenseExpireWarning extends TestCase {
	private static $user;
	private $original_user;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		$container  = apply_filters( 'rocket_container', null );
		self::$user = $container->get( 'user' );
	}

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'rocket_before_add_field_to_settings', 'add_license_expire_warning' );

		$this->original_user = $this->getNonPublicPropertyValue( 'user', self::$user, self::$user );
	}

	public function tear_down() {
		$this->set_reflective_property( $this->original_user, 'user', self::$user );

		$this->restoreWpFilter( 'rocket_before_add_field_to_settings' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $args, $expected ) {
		$this->white_label = $config['white_label'];

		$this->set_reflective_property( $config['transient'], 'user', self::$user );

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_before_add_field_to_settings', $args )
		);
	}
}
