<?php

namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN;

use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber::rocketcdn_field
 * @group  RocketCDN
 */
class Test_RocketcdnField extends TestCase {
	private $api_client;
	private $options;
	private $beacon;

	public function setUp() {
		parent::setUp();

		$this->api_client = $this->createMock( 'WP_Rocket\CDN\RocketCDN\APIClient' );
		$this->options    = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$this->beacon     = $this->createMock( 'WP_Rocket\Admin\Settings\Beacon' );
	}

	/**
	 * Test should return default array for the field when RocketCDN is not active
	 */
	public function testShouldReturnDefaultFieldWhenRocketCDNNotActive() {
		$this->api_client->method( 'get_subscription_data' )
		                 ->willReturn( [ 'is_active' => false ] );

		$fields = [
			'cdn_cnames' => [],
		];

		$page = new AdminPageSubscriber( $this->api_client, $this->options, $this->beacon, 'views/settings/rocketcdn' );
		$this->assertSame(
			$fields,
			$page->rocketcdn_field( $fields )
		);
	}

	/**
	 * Test should return the special array for the field when RocketCDN is active
	 */
	public function testShouldReturnRocketCDNFieldWhenRocketCDNActive() {
		$this->mockCommonWpFunctions();

		$this->api_client->method( 'get_subscription_data' )
		                 ->willReturn( [ 'is_active' => true ] );

		$fields = [
			'cdn_cnames' => [],
		];

		$rocketcdn_field = [
			'cdn_cnames' => [
				'type'        => 'rocket_cdn',
				'label'       => __( 'CDN CNAME(s)', 'rocket' ),
				'description' => __( 'Specify the CNAME(s) below', 'rocket' ),
				'helper'      => __( 'Rocket CDN is currently active. <a href="" data-beacon-article="" rel="noopener noreferrer" target="_blank">More Info</a>', 'rocket' ),
				'default'     => '',
				'section'     => 'cnames_section',
				'page'        => 'page_cdn',
			],
		];

		$page = new AdminPageSubscriber( $this->api_client, $this->options, $this->beacon, 'views/settings/rocketcdn' );
		$this->assertSame(
			$rocketcdn_field,
			$page->rocketcdn_field( $fields )
		);
	}
}
