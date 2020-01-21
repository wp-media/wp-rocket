<?php

namespace WP_Rocket\Tests\Unit\Subscriber\Addons\CloudflareSubscriber;

use WP_Rocket\Tests\Unit\TestCase;

abstract class CloudflareTestCase extends TestCase {

	protected function setUp() {
		parent::setUp();

		$this->mockCommonWpFunctions();
	}

	/**
	 * Get the mocks required by Cloudflare’s constructor.
	 *
	 * @since  3.5
	 * @author Soponar Cristina
	 * @access private
	 *
	 * @param integer $do_cloudflare      - Value to return for $options->get( 'do_cloudflare' ).
	 * @param string  $cloudflare_email   - Value to return for $options->get( 'cloudflare_email' ).
	 * @param string  $cloudflare_api_key - Value to return for $options->get( 'cloudflare_api_key' ).
	 * @param string  $cloudflare_zone_id - Value to return for $options->get( 'cloudflare_zone_id' ).
	 *
	 * @return array                      - Array of Mocks
	 */
	protected function getConstructorMocks( $do_cloudflare = 1, $cloudflare_email = '', $cloudflare_api_key = '', $cloudflare_zone_id = '' ) {
		$options      = $this->createMock( 'WP_Rocket\Admin\Options' );
		$options_data = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$map          = [
			[
				'do_cloudflare',
				'',
				$do_cloudflare,
			],
			[
				'cloudflare_email',
				null,
				$cloudflare_email,
			],
			[
				'cloudflare_api_key',
				null,
				$cloudflare_api_key,
			],
			[
				'cloudflare_zone_id',
				null,
				$cloudflare_zone_id,
			],
		];
		$options_data->method( 'get' )->will( $this->returnValueMap( $map ) );

		return [
			'options_data' => $options_data,
			'options'      => $options,
		];
	}
}
