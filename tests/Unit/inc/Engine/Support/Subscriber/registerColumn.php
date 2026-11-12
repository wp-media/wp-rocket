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
 * Test class covering \WP_Rocket\Engine\Support\Subscriber::register_column
 *
 * @group Support
 */
class TestRegisterColumn extends TestCase {
	private $options;
	private $user;
	private $subscriber;

	public function set_up() {
		parent::set_up();

		$this->options     = Mockery::mock( Options_Data::class );
		$this->user    = Mockery::mock( User::class );
		$this->subscriber  = new Subscriber( Mockery::mock( Rest::class ), Mockery::mock( Meta::class ), $this->options, $this->user );

		Functions\when( '__' )->returnArg();
		Functions\when( '_x' )->returnArg();
		Functions\when( 'rocket_valid_key' )->justReturn( true );
		$this->user->shouldReceive( 'can_use_ai' )->andReturn( true )->byDefault();
	}

	/**
	 * @dataProvider configTestData
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldDoExpected( $config, $expected ): void {
		if ( $config['wap_client_exists'] ) {
			/** @var \Mockery\MockInterface $wap_client The class is aliased, so PHPStan cannot resolve it. */
			$wap_client = Mockery::mock( 'alias:WapClient' );

			if ( $expected['registered'] ) {
				$expectation = $wap_client->shouldReceive( 'register_chat_column' )->once();

				if ( ! empty( $expected['config_check'] ) ) {
					$check = $expected['config_check'];
					$expectation->with(
						Mockery::on(
							function ( $cfg ) use ( $check ) {
								return ( ! isset( $check['product'] ) || $check['product'] === $cfg['product'] )
									&& ( ! isset( $check['page_context'] ) || $check['page_context'] === $cfg['page_context'] )
									&& ( ! isset( $check['grnd_license_key'] ) || $check['grnd_license_key'] === $cfg['grnd']['license_key'] )
									&& ( ! isset( $check['screens'] ) || $check['screens'] === $cfg['screens'] );
							}
						)
					);
				}
			} else {
				$wap_client->shouldNotReceive( 'register_chat_column' );
			}

			$this->options->shouldReceive( 'get' )->with( 'consumer_key', '' )->andReturn( $config['consumer_key'] ?? '' );
			$this->options->shouldReceive( 'get' )->with( 'consumer_email', '' )->andReturn( $config['consumer_email'] ?? '' );

			if ( array_key_exists( 'can_use_ai', $config ) ) {
				$this->user->shouldReceive( 'can_use_ai' )->andReturn( $config['can_use_ai'] );
			}
		} else {
			$this->options->shouldNotReceive( 'get' );
		}

		$this->subscriber->register_column();
	}
}
