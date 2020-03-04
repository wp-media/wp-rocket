<?php
namespace WP_Rocket\Tests\Unit\inc\classes\subscriber\Media\WebpSubscriber;

use Brain\Monkey\Filters;
use WP_Rocket\Subscriber\Media\Webp_Subscriber;

/**
 * @covers \WP_Rocket\Subscriber\Media\Webp_Subscriber::maybe_disable_setting_field
 * @group Subscriber
 */
class Test_MaybeDisableSettingField extends TestCase {

	public function testShouldReturnIdenticalWhenWebpCacheIsEnabled() {
		$cache_webp_field = [ 'foo' => 'bar' ];

		$mocks = $this->getConstructorMocks( 0 );

		$webpSubscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

		$this->assertSame( $cache_webp_field, $webpSubscriber->maybe_disable_setting_field( $cache_webp_field ) );
	}

	public function testShouldReturnIdenticalWhenWebpCacheIsDisabled() {
		// Make sure the filter to disable caching runs once with the expected output value.
		Filters\expectApplied( 'rocket_disable_webp_cache' )
			->times( 3 )
			->andReturn( true ); // Simulate a filter.

		$mocks = $this->getConstructorMocks( 0 );

		$webpSubscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

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
}
