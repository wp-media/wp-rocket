<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Context;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Engine\CDN\RocketCDN\APIClient;
use WP_Rocket\Tests\Unit\TestCase;

class Test_GetDriver extends TestCase {
	/**
	 * @var Options_Data
	 */
	private $options;

	/**
	 * @var APIClient
	 */
	private $api_client;

	/**
	 * @var Context
	 */
	private $context;

	public function set_up() {
		parent::set_up();

		$this->options    = Mockery::mock( Options_Data::class );
		$this->api_client = Mockery::mock( APIClient::class );
		$this->context    = new Context( $this->options, $this->api_client );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedDriver( array $config, string $expected ) {
		$this->options->expects()->get( 'cdn_type', 'rocketcdn' )->andReturn( $config['cdn_type'] );

		if ( 'rocketcdn' === $config['cdn_type'] ) {
			$this->api_client->expects()->get_subscription_data()->andReturn( $config['subscription'] );
		} else {
			$this->api_client->shouldNotReceive( 'get_subscription_data' );
		}

		$this->assertSame( $expected, $this->context->get_driver() );
	}
}
