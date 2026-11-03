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
 * Test class covering \WP_Rocket\Engine\Support\Subscriber::get_column_config
 * and \WP_Rocket\Engine\Support\Subscriber::get_rocket_capability
 *
 * @group Support
 */
class TestGetColumnConfig extends TestCase {
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

	public function testShouldMapTheWapCapabilityToWpRocketCapability() {
		$this->assertSame( 'rocket_manage_options', $this->subscriber->get_rocket_capability() );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ): void {
		$this->options->shouldReceive( 'get' )->with( 'consumer_key', '' )->andReturn( $config['consumer_key'] );
		$this->options->shouldReceive( 'get' )->with( 'consumer_email', '' )->andReturn( $config['consumer_email'] );

		if ( ! array_key_exists( 'user_data', $config ) ) {
			// byDefault from set_up applies.
		} elseif ( null === $config['user_data'] ) {
			$this->user_client->shouldNotReceive( 'get_user_data' );
		} else {
			$this->user_client->shouldReceive( 'get_user_data' )
			                  ->andReturn( false === $config['user_data'] ? false : (object) $config['user_data'] );
		}

		if ( isset( $config['valid_key'] ) ) {
			Functions\when( 'rocket_valid_key' )->justReturn( $config['valid_key'] );
		}

		if ( isset( $config['constants'] ) ) {
			$constants = $config['constants'];
			Functions\when( 'rocket_get_constant' )->alias(
				function ( $name, $default = null ) use ( $constants ) {
					return array_key_exists( $name, $constants ) ? $constants[ $name ] : $default;
				}
			);
		}

		$result = $this->subscriber->get_column_config();

		if ( $expected['is_empty'] ) {
			$this->assertSame( [], $result );
			return;
		}

		foreach ( $expected['fields'] as $key => $value ) {
			switch ( $key ) {
				case 'grnd_license_key':
					$this->assertSame( $value, $result['grnd']['license_key'], 'grnd.license_key' );
					break;
				case 'grnd_issuer_url':
					$this->assertSame( $value, $result['grnd']['issuer_url'], 'grnd.issuer_url' );
					break;
				case 'show_agent_selector':
					$this->assertSame( $value, $result['layout']['showAgentSelector'], 'layout.showAgentSelector' );
					break;
				default:
					$this->assertSame( $value, $result[ $key ], $key );
			}
		}
	}
}
