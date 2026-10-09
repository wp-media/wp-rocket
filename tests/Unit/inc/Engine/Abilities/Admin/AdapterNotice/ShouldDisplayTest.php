<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Abilities\Admin\AdapterNotice;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Abilities\Admin\AdapterNotice;
use WP_Rocket\Engine\Abilities\Context;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Tests for WP_Rocket\Engine\Abilities\Admin\AdapterNotice::should_display()
 *
 * @group Abilities
 */
class ShouldDisplayTest extends TestCase {
	/**
	 * Checks every display condition.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnExpected( array $config, bool $expected ): void {
		$context = Mockery::mock( Context::class );
		$context->shouldReceive( 'is_enabled' )->andReturn( $config['abilities_enabled'] );

		Functions\when( 'current_user_can' )->alias(
			static function ( $cap ) use ( $config ) {
				return in_array( $cap, $config['caps'], true );
			}
		);
		Functions\when( 'get_current_user_id' )->justReturn( 1 );
		Functions\when( 'get_current_blog_id' )->justReturn( $config['blog_id'] ?? 1 );
		Functions\when( 'maybe_unserialize' )->alias(
			static function ( $data ) {
				return is_string( $data ) ? unserialize( $data, [ 'allowed_classes' => false ] ) : $data; // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
			}
		);
		Functions\when( 'get_user_meta' )->alias(
			static function ( $user_id, $key = '' ) use ( $config ) {
				if ( '' === $key ) {
					return $config['user_meta'];
				}

				return 'rocket_boxes' === $key ? $config['boxes'] : '';
			}
		);

		$notice = Mockery::mock( AdapterNotice::class . '[is_adapter_loaded]', [ $context ] )
			->shouldAllowMockingProtectedMethods();
		$notice->shouldReceive( 'is_adapter_loaded' )->andReturn( $config['adapter_loaded'] );

		$this->assertSame( $expected, $notice->should_display() );
	}
}
