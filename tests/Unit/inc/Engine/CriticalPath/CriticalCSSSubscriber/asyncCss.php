<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\CriticalCSSSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber::async_css
 *
 * @group  Subscribers
 * @group  CriticalPath
 */
class Test_AsyncCss extends TestCase {
	use SubscriberTrait;

	public function setUp() {
		parent::setUp();
		$this->setUpTests();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldAsyncCss( $config, $expected ) {
		if ( ! empty( $config['constants'] ) ) {
			foreach ( $config['constants'] as $constant_name => $constant_value ) {
				$constant_name = strtolower($constant_name);
				$this->$constant_name = $constant_value;
			}
		}

		if ( ! empty( $config['options'] ) ) {
			foreach ( $config['options'] as $option_key => $option_value ) {
				$this->options->shouldReceive( 'get' )->with( $option_key )->andReturn( $option_value );
			}
		}

		if ( ! empty( $config['exclude_options'] ) ) {
			foreach ($config['exclude_options'] as $exclude_option => $return) {
				Functions\expect( 'is_rocket_post_excluded_option' )->with( $exclude_option )->andReturn( $return );
			}
		}

		$exclude_css_files = isset( $config['exclude_css_files'] ) ? $config['exclude_css_files'] : [];

		$this->critical_css->shouldReceive( 'get_exclude_async_css' )->andReturn( $exclude_css_files );
		Functions\expect( 'rocket_extract_url_component' )->andReturnUsing( function( $url, $component) {
			if ( PHP_URL_PATH === $component ) {
				$parsed_url = parse_url($url);
				return $parsed_url['path'];
			}
			return '';
		} );

		$actual = $this->subscriber->async_css( $config['html'] );
		$this->assertEquals( $expected['html'], $actual );
	}
}
