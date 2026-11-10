<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Support\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\Support\Meta;
use WP_Rocket\Engine\Support\Rest;
use WP_Rocket\Engine\Support\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Support\Subscriber::maybe_display_unavailable_notice
 *
 * @group Support
 */
class TestMaybeDisplayUnavailableNotice extends TestCase {
	private $options;
	private $user;
	private $subscriber;

	public function set_up() {
		parent::set_up();

		if ( ! defined( 'WP_ROCKET_PLUGIN_NAME' ) ) {
			define( 'WP_ROCKET_PLUGIN_NAME', 'WP Rocket' );
		}

		$this->options     = Mockery::mock( Options_Data::class );
		$this->user    = Mockery::mock( User::class );
		$this->subscriber  = new Subscriber( Mockery::mock( Rest::class ), Mockery::mock( Meta::class ), $this->options, $this->user );

		Functions\when( 'rocket_valid_key' )->justReturn( true );
		$this->user->shouldReceive( 'can_use_ai' )->andReturn( true )->byDefault();
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( '__' )->returnArg();
	}

	/**
	 * @dataProvider configTestData
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldDoExpected( $config, $expected ): void {
		if ( $config['wap_class_exists'] ) {
			/** @var \Mockery\MockInterface $manager The class is aliased, so PHPStan cannot resolve it. */
			$manager = Mockery::mock( 'alias:WP_Rocket\Dependencies\GroupOne\WapClient\AppPasswordManager' );

			if ( $config['wap_check_called'] ) {
				$manager->shouldReceive( 'are_app_passwords_available' )
						->andReturn( $config['app_passwords_available'] );
				$manager->shouldReceive( 'is_https_missing' )
						->andReturn( $config['https_missing'] ?? false );
			} else {
				$manager->shouldNotReceive( 'are_app_passwords_available' );
			}

			$this->options->shouldReceive( 'get' )
						  ->with( 'consumer_key', '' )
						  ->andReturn( $config['consumer_key'] ?? '' );
			$this->options->shouldReceive( 'get' )
						  ->with( 'consumer_email', '' )
						  ->andReturn( $config['consumer_email'] ?? '' );
		} else {
			$this->options->shouldNotReceive( 'get' );
		}

		if ( isset( $config['current_user_can'] ) ) {
			Functions\when( 'current_user_can' )->justReturn( $config['current_user_can'] );
		}

		if ( isset( $config['can_use_ai'] ) ) {
			$this->user->shouldReceive( 'can_use_ai' )->andReturn( $config['can_use_ai'] );
		}

		Functions\when( 'get_current_screen' )->justReturn(
			null !== $config['screen_id'] ? (object) [ 'id' => $config['screen_id'] ] : null
		);

		if ( $expected['notice_called'] ) {
			Functions\expect( 'rocket_notice_html' )
				->once()
				->with(
					Mockery::on(
						function ( $args ) use ( $expected ) {
							return $expected['status'] === $args['status']
								&& false !== strpos( $args['message'], $expected['message_contains'] );
						}
					)
				);
		} else {
			Functions\expect( 'rocket_notice_html' )->never();
		}

		$this->subscriber->maybe_display_unavailable_notice();
	}
}
