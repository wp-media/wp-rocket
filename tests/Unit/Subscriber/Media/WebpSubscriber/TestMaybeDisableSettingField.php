<?php
namespace WP_Rocket\Tests\Unit\Subscriber\Media\WebpSubscriber;

use WP_Rocket\Subscriber\Media\Webp_Subscriber;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

class TestMaybeDisableSettingField extends TestCase {
	/**
	 * Test Webp_Subscriber->maybe_disable_setting_field() should return the identical array when webp cache is enabled.
	 */
	public function testShouldReturnIdenticalWhenWebpCacheIsEnabled() {
		$cache_webp_field = [ 'foo' => 'bar' ];

		$mocks = $this->getConstructorMocks( 0 );

		$webpSubscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'] );

		$this->assertSame( $cache_webp_field, $webpSubscriber->maybe_disable_setting_field( $cache_webp_field ) );
	}

	/**
	 * Test Webp_Subscriber->maybe_disable_setting_field() should return the identical array when webp cache is disabled.
	 */
	public function testShouldReturnIdenticalWhenWebpCacheIsDisabled() {
		// Make sure the filter to disable caching runs once with the expected output value.
		Filters\expectApplied( 'rocket_disable_webp_cache' )
			->times( 3 )
			->andReturn( true ); // Simulate a filter.

		$mocks = $this->getConstructorMocks( 0 );

		$webpSubscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'] );

		$cache_webp_field = [ 'foo' => 'bar' ];
		$expected_field   = [
			'foo'             => 'bar',
			'input_attr'      => [
				'disabled' => 1,
			],
			'container_class' => [
				'wpr-isDisabled',
				'wpr-isParent',
			],
		];

		$this->assertSame( $expected_field, $webpSubscriber->maybe_disable_setting_field( $cache_webp_field ) );

		$cache_webp_field = [
			'foo'             => 'bar',
			'input_attr'      => [
				'data-foo' => 'bar',
				'disabled' => 0,
			],
			'container_class' => [
				'oh-no',
			],
		];
		$expected_field   = [
			'foo'             => 'bar',
			'input_attr'      => [
				'data-foo' => 'bar',
				'disabled' => 1,
			],
			'container_class' => [
				'oh-no',
				'wpr-isDisabled',
				'wpr-isParent',
			],
		];

		$this->assertSame( $expected_field, $webpSubscriber->maybe_disable_setting_field( $cache_webp_field ) );

		$cache_webp_field = [
			'foo'             => 'bar',
			'input_attr'      => 'bar',
			'container_class' => 'everything-is-bar',
		];
		$expected_field   = [
			'foo'             => 'bar',
			'input_attr'      => [
				'disabled' => 1,
			],
			'container_class' => [
				'wpr-isDisabled',
				'wpr-isParent',
			],
		];

		$this->assertSame( $expected_field, $webpSubscriber->maybe_disable_setting_field( $cache_webp_field ) );
	}

	/**
	 * Get the 3 mocks required by Webp_Subscriber’s constructor.
	 *
	 * @since  3.4
	 * @author Grégory Viguier
	 * @access private
	 *
	 * @param  int   $cache_webp_option_value Value to return for $mocks['optionsData']->get( 'cache_webp' ).
	 * @param  array $cdn_hosts               An array of URL hosts.
	 * @return array An array containing the 3 mocks.
	 */
	private function getConstructorMocks( $cache_webp_option_value = 1, $cdn_hosts = [ 'cdn-example.net' ] ) {
		// Mock the 3 required objets for Webp_Subscriber.
		$mocks = [
			'optionsData' => $this->createMock( 'WP_Rocket\Admin\Options_Data' ),
			'optionsApi'  => $this->createMock( 'WP_Rocket\Admin\Options' ),
			'cdn'         => $this->createMock( 'WP_Rocket\Subscriber\CDN\CDNSubscriber' ),
		];

		$mocks['optionsData']
			->method( 'get' )
			->will(
				$this->returnValueMap(
					[
						[ 'cache_webp', '', $cache_webp_option_value ],
					]
				)
			);

		$mocks['cdn']
			->method( 'get_cdn_hosts' )
			->will(
				$this->returnValueMap(
					[
						[ [], [ 'all', 'images' ], $cdn_hosts ],
					]
				)
			);

		return $mocks;
	}
}
