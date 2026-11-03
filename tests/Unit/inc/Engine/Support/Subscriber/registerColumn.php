<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Support\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\License\API\UserClient;
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
	private $user_client;
	private $subscriber;

	public function set_up() {
		parent::set_up();

		$this->options     = Mockery::mock( Options_Data::class );
		$this->user_client = Mockery::mock( UserClient::class );
		$this->subscriber  = new Subscriber( Mockery::mock( Rest::class ), Mockery::mock( Meta::class ), $this->options, $this->user_client );

		Functions\when( 'rocket_valid_key' )->justReturn( true );
		$this->user_client->shouldReceive( 'get_user_data' )->andReturn( (object) [ 'can_use_ai' => true ] )->byDefault();
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

			if ( array_key_exists( 'user_data', $config ) ) {
				$this->user_client->shouldReceive( 'get_user_data' )
				                  ->andReturn( (object) $config['user_data'] );
			}
		} else {
			$this->options->shouldNotReceive( 'get' );
		}

		$this->subscriber->register_column();
	}
}
