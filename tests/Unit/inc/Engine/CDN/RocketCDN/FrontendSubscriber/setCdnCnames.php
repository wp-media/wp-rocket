<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\FrontendSubscriber;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\RocketCDN\APIClient;
use WP_Rocket\Engine\CDN\RocketCDN\FrontendSubscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\FrontendSubscriber::set_cdn_cnames
 *
 * @group RocketCDN
 */
class Test_SetCdnCnames extends TestCase {

	/**
	 * @var Options_Data
	 */
	private $options;

	/**
	 * @var APIClient
	 */
	private $api_client;

	/**
	 * @var FrontendSubscriber
	 */
	private $subscriber;

	public function set_up() {
		parent::set_up();

		$this->options    = Mockery::mock( Options_Data::class );
		$this->api_client = Mockery::mock( APIClient::class );
		$this->subscriber = new FrontendSubscriber( $this->options, $this->api_client );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedCnames( $config, $expected ) {
		$this->options->shouldReceive( 'get' )
			->with( 'cdn_type' )
			->andReturn( $config['cdn_type'] );

		if ( 'rocketcdn' === $config['cdn_type'] ) {
			$this->api_client->shouldReceive( 'get_subscription_data' )
				->andReturn( $config['subscription_data'] );
		}

		$result = $this->subscriber->set_cdn_cnames( null );

		$this->assertSame( $expected['cdn_cnames'], $result );
	}
}
