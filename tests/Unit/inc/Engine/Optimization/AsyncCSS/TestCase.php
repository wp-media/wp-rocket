<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\AsyncCSS;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\AsyncCSS;
use WP_Rocket\Tests\Unit\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase {
	protected $dom;
	protected $instance;
	protected $options;

	protected $default_config = [
		'excluded_hrefs' => [],
		'xpath_query'    => '@rel="stylesheet" and not(contains(@href,\'fonts.googleapis.com\'))'
	];

	protected function setUp() {
		parent::setUp();

		Functions\expect( 'get_current_blog_id' )->andReturn( 1 );
		Functions\when( 'home_url' )->justReturn( 'https://example.org/' );

		$this->options = Mockery::mock( Options_Data::class );
	}


	protected function setUpTest( $html, array $config = [] ) {
		$config = $this->initConfig( $config );

		$this->instance = AsyncCSS::from_html( $this->options, $html, $config['excluded_hrefs'], $config['xpath_query'] );

		if ( ! empty( $this->instance ) ) {
			$this->dom = $this->getNonPublicPropertyValue( 'dom', AsyncCSS::class, $this->instance );
		}
	}

	protected function initConfig( $config ) {
		if ( empty( $config ) ) {
			return $this->default_config;
		}

		if ( isset( $config['use_default'] ) && $config['use_default'] ) {
			unset( $config['use_default'] );

			return array_merge_recursive(
				$this->default_config,
				$config
			);
		}

		return array_merge(
			[
				'excluded_hrefs' => [],
				'xpath_query'    => '',
			],
			$config
		);
	}
}
