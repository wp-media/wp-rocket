<?php

namespace WP_Rocket\Tests\Integration\Subscriber\CDN\RocketCDN;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber::rocketcdn_field
 * @group  RocketCDN
 * @group  AdminOnly
 */
class Test_RocketcdnField extends TestCase {
	private static $fields;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$fields = [
			'cdn'              => [
				'type'              => 'checkbox',
				'label'             => 'Enable Content Delivery Network',
				'helper'            => '',
				'section'           => 'cdn_section',
				'page'              => 'page_cdn',
				'default'           => 0,
				'sanitize_callback' => 'sanitize_checkbox',
			],
			'cdn_cnames'       => [
				'type'        => 'cnames',
				'label'       => 'CDN CNAME(s)',
				'description' => 'Specify the CNAME(s) below',
				'default'     => [],
				'section'     => 'cnames_section',
				'page'        => 'page_cdn',
			],
			'cdn_reject_files' => [
				'type'              => 'textarea',
				'description'       => 'Specify URL(s) of files that should not get served via CDN (one per line).',
				'helper'            => 'The domain part of the URL will be stripped automatically.<br>Use (.*) wildcards to exclude all files of a given file type located at a specific path.',
				'placeholder'       => '/wp-content/plugins/some-plugins/(.*).css',
				'section'           => 'exclude_cdn_section',
				'page'              => 'page_cdn',
				'default'           => [],
				'sanitize_callback' => 'sanitize_textarea',
			],
			'rocketcdn_token' => [
				'type'            => 'text',
				'label'           => 'RocketCDN token',
				'description'     => __( 'The RocketCDN token used to send request to RocketCDN API', 'rocket' ),
				'default'         => '',
				'container_class' => [
					'wpr-rocketcdn-token',
					'wpr-isHidden',
				],
				'section'         => 'cnames_section',
				'page'            => 'page_cdn',
			],
		];
	}

	public function setUp() {
		parent::setUp();

		set_current_screen( 'settings_page_wprocket' );
	}

	public function tearDown() {
		parent::tearDown();

		delete_transient( 'rocketcdn_status' );
	}

	/**
	 * Test should return default array for the field when RocketCDN is not active
	 */
	public function testShouldReturnDefaultFieldWhenRocketCDNNotActive() {
		set_transient( 'rocketcdn_status', [ 'subscription_status' => 'cancelled', 'cdn_url' => '' ], MINUTE_IN_SECONDS );

		$this->assertSame( self::$fields, apply_filters( 'rocket_cdn_settings_fields', self::$fields ) );
	}

	/**
	 * Test should return the special array for the field when RocketCDN is active.
	 */
	public function testShouldReturnRocketCDNFieldWhenRocketCDNActive() {
		set_transient( 'rocketcdn_status', [ 'subscription_status' => 'running', 'cdn_url' => 'example1.org' ], MINUTE_IN_SECONDS );

		$expected               = self::$fields;
		$expected['cdn_cnames'] = [
			'type'        => 'rocket_cdn',
			'label'       => 'CDN CNAME(s)',
			'description' => 'Specify the CNAME(s) below',
			'helper'      => 'Rocket CDN is currently active. <a href="" data-beacon-article="" rel="noopener noreferrer" target="_blank">More Info</a>',
			'default'     => '',
			'section'     => 'cnames_section',
			'page'        => 'page_cdn',
		];

		$this->assertSame( $expected, apply_filters( 'rocket_cdn_settings_fields', self::$fields ) );
	}

	/**
	 * Test should return the special array with CNAME for the field when RocketCDN is active and there is(are) a
	 * different CDN CNAME(s).
	 */
	public function testShouldReturnRocketCDNFieldWithCNAMEWhenRocketCDNActiveAndCNames() {
		$cdn_names_cb = function(){
			return [ 'example2.com' ];
		};
		add_filter( 'pre_get_rocket_option_cdn_cnames', $cdn_names_cb );
		set_transient( 'rocketcdn_status', [ 'subscription_status' => 'running', 'cdn_url' => 'example1.org' ], MINUTE_IN_SECONDS );

		$expected               = self::$fields;
		$expected['cdn_cnames'] = [
			'type'        => 'rocket_cdn',
			'label'       => 'CDN CNAME(s)',
			'description' => 'Specify the CNAME(s) below',
			'helper'      => 'To use Rocket CDN, replace your CNAME with <code>example1.org</code>. <a href="" data-beacon-article="" rel="noopener noreferrer" target="_blank">More Info</a>',
			'default'     => '',
			'section'     => 'cnames_section',
			'page'        => 'page_cdn',
		];

		$this->assertSame( $expected, apply_filters( 'rocket_cdn_settings_fields', self::$fields ) );
		remove_filter( 'pre_get_rocket_option_cdn_cnames', $cdn_names_cb );
	}
}
