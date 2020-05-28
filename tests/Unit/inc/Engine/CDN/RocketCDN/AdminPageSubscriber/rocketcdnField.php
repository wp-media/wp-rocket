<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\AdminPageSubscriber;

use Mockery;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Engine\CDN\RocketCDN\AdminPageSubscriber;
use WP_Rocket\Tests\StubTrait;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\AdminPageSubscriber::rocketcdn_field
 * @group  RocketCDN
 */
class Test_RocketcdnField extends TestCase {
	use StubTrait;

	protected static $mockCommonWpFunctionsInSetUp = true;
	private $api_client;
	private $options;
	private $beacon;
	private $page;

	public function setUp() {
		parent::setUp();

		$this->stubRocketGetConstant();

		$this->api_client = Mockery::mock( 'WP_Rocket\Engine\CDN\RocketCDN\APIClient' );
		$this->options    = Mockery::mock( 'WP_Rocket\Admin\Options_Data' );
		$this->beacon     = Mockery::mock( 'WP_Rocket\Engine\Admin\Beacon\Beacon' );
		$this->page       = new AdminPageSubscriber(
			$this->api_client,
			$this->options,
			$this->beacon,
			'views/settings/rocketcdn'
		);
	}

	public function tearDown() {
		$this->resetStubProperties();

		parent::tearDown();
	}

	public function testShouldReturnDefaultFieldWhenWhiteLabel() {
		$this->white_label = true;

		$fields = [ 'cdn_cnames' => [] ];
		$this->assertSame( $fields, $this->page->rocketcdn_field( $fields ) );
	}

	public function testShouldReturnDefaultFieldWhenRocketCDNNotActive() {
		$this->api_client->shouldReceive( 'get_subscription_data' )
						 ->once()
						 ->andReturn( [ 'subscription_status' => 'cancelled' ] );
		$this->options->shouldReceive( 'get' )->never();

		$fields = [ 'cdn_cnames' => [] ];
		$this->assertSame( $fields, $this->page->rocketcdn_field( $fields ) );
	}

	public function testShouldReturnRocketCDNFieldWhenRocketCDNActive() {
		$this->api_client->shouldReceive( 'get_subscription_data' )
						 ->once()
						 ->andReturn( [ 'subscription_status' => 'running', 'cdn_url' => 'example1.org' ] );
		$this->options->shouldReceive( 'get' )
					  ->once()
					  ->with( 'cdn_cnames', [] )
					  ->andReturn( [ 'example1.org' ] );
		$this->beacon->shouldReceive( 'get_suggest' )
					 ->once()
					 ->andReturn( [
						'url' => 'https://docs.wp-rocket.me/article/1307-rocketcdn',
						'id'  => '5e4c84bd04286364bc958833',
					 ] );

		$expected = [
			'cdn_cnames' => [
				'type'        => 'rocket_cdn',
				'label'       => 'CDN CNAME(s)',
				'description' => 'Specify the CNAME(s) below',
				'helper'      => 'Your RocketCDN subscription is currently active. <a href="https://docs.wp-rocket.me/article/1307-rocketcdn" data-beacon-article="5e4c84bd04286364bc958833" rel="noopener noreferrer" target="_blank">More Info</a>',
				'default'     => '',
				'section'     => 'cnames_section',
				'page'        => 'page_cdn',
				'beacon'      => [
					'url' => 'https://docs.wp-rocket.me/article/1307-rocketcdn',
					'id'  => '5e4c84bd04286364bc958833',
				],
			],
		];

		$this->assertSame( $expected, $this->page->rocketcdn_field( [ 'cdn_cnames' => [] ] ) );
	}

	public function testShouldReturnRocketCDNFieldWithCNAMEWhenRocketCDNActiveAndCNamesEmpty() {
		$this->api_client->shouldReceive( 'get_subscription_data' )
						 ->once()
		                 ->andReturn( [ 'subscription_status' => 'running', 'cdn_url' => 'example1.org' ] );
		$this->options->shouldReceive( 'get' )
					  ->once()
		              ->with( 'cdn_cnames', [] )
					  ->andReturn( [] );
		$this->beacon->shouldReceive( 'get_suggest' )
					  ->once()
					  ->andReturn( [
						'url' => 'https://docs.wp-rocket.me/article/1307-rocketcdn',
						'id'  => '5e4c84bd04286364bc958833',
					  ] );

		$expected = [
			'cdn_cnames' => [
				'type'        => 'rocket_cdn',
				'label'       => 'CDN CNAME(s)',
				'description' => 'Specify the CNAME(s) below',
				'helper'      => 'To use RocketCDN, replace your CNAME with <code>example1.org</code>. <a href="https://docs.wp-rocket.me/article/1307-rocketcdn" data-beacon-article="5e4c84bd04286364bc958833" rel="noopener noreferrer" target="_blank">More Info</a>',
				'default'     => '',
				'section'     => 'cnames_section',
				'page'        => 'page_cdn',
				'beacon'      => [
					'url' => 'https://docs.wp-rocket.me/article/1307-rocketcdn',
					'id'  => '5e4c84bd04286364bc958833',
				],
			],
		];

		$this->assertSame( $expected, $this->page->rocketcdn_field( [ 'cdn_cnames' => [] ] ) );
	}

	public function testShouldReturnRocketCDNFieldWithCNAMEWhenRocketCDNActiveAndCNames() {
		$this->api_client->shouldReceive( 'get_subscription_data' )
						 ->once()
		                 ->andReturn( [ 'subscription_status' => 'running', 'cdn_url' => 'example1.org' ] );
		$this->options->shouldReceive( 'get' )
					  ->once()
		              ->with( 'cdn_cnames', [] )
					  ->andReturn( [ 'example2.com' ] );
		$this->beacon->shouldReceive( 'get_suggest' )
					  ->once()
					  ->andReturn( [
						'url' => 'https://docs.wp-rocket.me/article/1307-rocketcdn',
						'id'  => '5e4c84bd04286364bc958833',
					  ] );

		$expected = [
			'cdn_cnames' => [
				'type'        => 'rocket_cdn',
				'label'       => 'CDN CNAME(s)',
				'description' => 'Specify the CNAME(s) below',
				'helper'      => 'To use RocketCDN, replace your CNAME with <code>example1.org</code>. <a href="https://docs.wp-rocket.me/article/1307-rocketcdn" data-beacon-article="5e4c84bd04286364bc958833" rel="noopener noreferrer" target="_blank">More Info</a>',
				'default'     => '',
				'section'     => 'cnames_section',
				'page'        => 'page_cdn',
				'beacon'      => [
					'url' => 'https://docs.wp-rocket.me/article/1307-rocketcdn',
					'id'  => '5e4c84bd04286364bc958833',
				],
			],
		];

		$this->assertSame( $expected, $this->page->rocketcdn_field( [ 'cdn_cnames' => [] ] ) );
	}
}
