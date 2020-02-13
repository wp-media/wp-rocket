<?php

namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN;

use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber::rocketcdn_field
 * @group  RocketCDN
 */
class Test_RocketcdnField extends TestCase {
	private $api_client;
	private $options;
	private $page;

	public function setUp() {
		parent::setUp();

		$this->api_client = $this->createMock( 'WP_Rocket\CDN\RocketCDN\APIClient' );
		$this->options    = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$this->page       = new AdminPageSubscriber(
			$this->api_client,
			$this->options,
			$this->createMock( 'WP_Rocket\Admin\Settings\Beacon' ),
			'views/settings/rocketcdn'
		);

		$this->mockCommonWpFunctions();
	}

	/**
	 * Test should return default array for the field when RocketCDN is not active
	 */
	public function testShouldReturnDefaultFieldWhenRocketCDNNotActive() {
		$this->api_client->expects( $this->once() )
		                 ->method( 'get_subscription_data' )
		                 ->willReturn( [ 'subscription_status' => 'cancelled' ] );
		$this->options->expects( $this->never() )->method( 'get' );

		$fields = [ 'cdn_cnames' => [] ];
		$this->assertSame( $fields, $this->page->rocketcdn_field( $fields ) );
	}

	/**
	 * Test should return the special array for the field when RocketCDN is active.
	 */
	public function testShouldReturnRocketCDNFieldWhenRocketCDNActive() {
		$this->api_client->expects( $this->once() )
		                 ->method( 'get_subscription_data' )
		                 ->willReturn( [ 'subscription_status' => 'running', 'cdn_url' => 'example1.org' ] );
		$this->options->expects( $this->once() )
		              ->method( 'get' )
		              ->with( 'cdn_cnames', [] )
		              ->willReturn( [ 'example1.org' ] );
		$expected = [
			'cdn_cnames' => [
				'type'        => 'rocket_cdn',
				'label'       => 'CDN CNAME(s)',
				'description' => 'Specify the CNAME(s) below',
				'helper'      => 'Rocket CDN is currently active. <a href="" data-beacon-article="" rel="noopener noreferrer" target="_blank">More Info</a>',
				'default'     => '',
				'section'     => 'cnames_section',
				'page'        => 'page_cdn',
			],
		];

		$this->assertSame( $expected, $this->page->rocketcdn_field( [ 'cdn_cnames' => [] ] ) );
	}

	/**
	 * Test should return the special array with CNAME for the field when RocketCDN is active and the field is empty.
	 */
	public function testShouldReturnRocketCDNFieldWithCNAMEWhenRocketCDNActiveAndCNamesEmpty() {
		$this->api_client->expects( $this->once() )
		                 ->method( 'get_subscription_data' )
		                 ->willReturn( [ 'subscription_status' => 'running', 'cdn_url' => 'example1.org' ] );
		$this->options->expects( $this->once() )
		              ->method( 'get' )
		              ->with( 'cdn_cnames', [] )
		              ->willReturn( [] );
		$expected = [
			'cdn_cnames' => [
				'type'        => 'rocket_cdn',
				'label'       => 'CDN CNAME(s)',
				'description' => 'Specify the CNAME(s) below',
				'helper'      => 'To use Rocket CDN, replace your CNAME with <code>example1.org</code>. <a href="" data-beacon-article="" rel="noopener noreferrer" target="_blank">More Info</a>',
				'default'     => '',
				'section'     => 'cnames_section',
				'page'        => 'page_cdn',
			],
		];

		$this->assertSame( $expected, $this->page->rocketcdn_field( [ 'cdn_cnames' => [] ] ) );
	}

	/**
	 * Test should return the special array with CNAME for the field when RocketCDN is active and there is(are) a
	 * different CDN CNAME(s).
	 */
	public function testShouldReturnRocketCDNFieldWithCNAMEWhenRocketCDNActiveAndCNames() {
		$this->api_client->expects( $this->once() )
		                 ->method( 'get_subscription_data' )
		                 ->willReturn( [ 'subscription_status' => 'running', 'cdn_url' => 'example1.org' ] );
		$this->options->expects( $this->once() )
		              ->method( 'get' )
		              ->with( 'cdn_cnames', [] )
		              ->willReturn( [ 'example2.com' ] );
		$expected = [
			'cdn_cnames' => [
				'type'        => 'rocket_cdn',
				'label'       => 'CDN CNAME(s)',
				'description' => 'Specify the CNAME(s) below',
				'helper'      => 'To use Rocket CDN, replace your CNAME with <code>example1.org</code>. <a href="" data-beacon-article="" rel="noopener noreferrer" target="_blank">More Info</a>',
				'default'     => '',
				'section'     => 'cnames_section',
				'page'        => 'page_cdn',
			],
		];

		$this->assertSame( $expected, $this->page->rocketcdn_field( [ 'cdn_cnames' => [] ] ) );
	}
}
