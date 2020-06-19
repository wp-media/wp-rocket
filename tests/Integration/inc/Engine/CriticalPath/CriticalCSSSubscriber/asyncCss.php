<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\CriticalCSSSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CriticalPath\CriticalCSS;
use WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration;
use WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber;
use WP_Rocket\Engine\CriticalPath\ProcessorService;
use WP_Rocket\Tests\Integration\TestCase;
use Mockery;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber::async_css
 *
 * @group  Subscribers
 * @group  CriticalPath
 * @group  AsyncCSS
 */
class Test_AsyncCss extends TestCase {

	private $rocket_options = [];

	private $to_be_removed = [
		'filters' => [

		]
	];

	private $exclude_css_files = [];

	private $options;
	private $subscriber;
	private $critical_css;
	private $processor_service;

	public function setUp()
	{
		parent::setUp();

		$this->options           = new Options_Data( [] );
		$this->critical_css      = new CriticalCSS(
			Mockery::mock( CriticalCSSGeneration::class ),
			$this->options,
			$this->filesystem
		);

		$this->processor_service = Mockery::mock( ProcessorService::class );
		$this->subscriber        = new CriticalCSSSubscriber( $this->critical_css, $this->processor_service, $this->options, $this->filesystem );
	}

	public function tearDown()
	{
		if ( ! empty( $this->to_be_removed ) ){
			foreach ( $this->to_be_removed as $key => $items ) {
				switch ($key) {
					case 'filters':
						foreach ($items as $filter_name => $filter_callback) {
							remove_filter($filter_name, $filter_callback);
						}
						break;
				}
			}
		}
		parent::tearDown();
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

		$this->rocket_options = $config['options'];
		if ( ! empty( $config['options'] ) ) {
			foreach ( $config['options'] as $option_key => $option_value ) {
				add_filter('pre_get_rocket_option_'.$option_key, [$this, 'setOption_'.$option_key]);
				$this->to_be_removed['filters'][] = [
					'pre_get_rocket_option_'.$option_key => [$this, 'setOption_'.$option_key]
				];
			}
		}

		if ( ! empty( $config['exclude_options'] ) ) {
			foreach ($config['exclude_options'] as $exclude_option => $return) {
				Functions\expect( 'is_rocket_post_excluded_option' )->with( $exclude_option )->andReturn( $return );
			}
		}

		$this->exclude_css_files = isset( $config['exclude_css_files'] ) ? $config['exclude_css_files'] : [];
		if ( ! empty( $this->exclude_css_files ) ) {
			add_filter( 'rocket_exclude_async_css', [$this, 'getExcludedCssFiles'] );
			$this->to_be_removed['filters'][] = [
				'rocket_exclude_async_css' => [$this, 'getExcludedCssFiles']
			];
		}

		$actual = $this->subscriber->async_css( $config['html'] );
		$this->assertEquals( $expected['html'], $actual );
	}

	public function setOption_async_css() {
		return $this->rocket_options['async_css'];
	}

	public function getExcludedCssFiles( ) {
		return $this->exclude_css_files;
	}
}
