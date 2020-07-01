<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\AsyncCSS;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CriticalPath\AsyncCSS;
use WP_Rocket\Engine\CriticalPath\CriticalCSS;
use WP_Rocket\Tests\Unit\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase {
	protected $critical_css;
	protected $dom;
	protected $instance;
	protected $options;

	protected $default_config = [
		'options'      => [ 'async_css' => 1 ],
		'critical_css' => [
			'get_current_page_critical_css' => 'page.css',
			'get_exclude_async_css'         => [],
		],
		'functions'    => [ 'is_rocket_post_excluded_option' => false ],
	];

	protected function setUp() {
		parent::setUp();

		Functions\expect( 'get_current_blog_id' )->andReturn( 1 );
		Functions\when( 'home_url' )->justReturn( 'https://example.org/' );

		$this->options      = Mockery::mock( Options_Data::class );
		$this->critical_css = Mockery::mock( CriticalCSS::class );
	}


	protected function setUpTest( $html, array $config = [] ) {
		$config = $this->initConfig( $config );

		$this->assertOptions( $config['options'] );
		$this->assertCriticalCss( $config['critical_css'] );
		$this->assertFunctions( $config['functions'] );

		$this->instance = AsyncCSS::from_html( $this->critical_css, $this->options, $html );

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
				'options'      => [],
				'critical_css' => [],
				'functions'    => [],
			],
			$config
		);
	}

	protected function assertOptions( $config ) {
		$this->options
			->shouldReceive( 'get' )
			->once()
			->with( 'async_css', 0 )
			->andReturn(
				isset( $config['async_css'] ) ? $config['async_css'] : false
			);

		if ( isset( $config['critical_css'] ) ) {
			$this->options
				->shouldReceive( 'get' )
				->once()
				->with( 'critical_css', '' )
				->andReturn(
					isset( $config['critical_css'] ) ? $config['critical_css'] : ''
				);
		} else {
			$this->options->shouldReceive( 'get' )->with( 'critical_css', '' )->never();
		}
	}

	protected function assertCriticalCss( $config ) {
		if ( isset( $config['get_current_page_critical_css'] ) ) {
			$this->critical_css
				->shouldReceive( 'get_current_page_critical_css' )
				->once()
				->andReturn( $config['get_current_page_critical_css'] );
		} else {
			$this->critical_css->shouldReceive( 'get_current_page_critical_css' )->never();
		}

		if ( isset( $config['get_exclude_async_css'] ) ) {
			$this->critical_css
				->shouldReceive( 'get_exclude_async_css' )
				->once()
				->andReturn( $config['get_exclude_async_css'] );
		} else {
			$this->critical_css->shouldReceive( 'get_exclude_async_css' )->never();
		}
	}

	protected function assertFunctions( $config ) {
		if ( isset( $config['is_rocket_post_excluded_option'] ) ) {
			Functions\expect( 'is_rocket_post_excluded_option' )
				->once()
				->with( 'async_css' )
				->andReturn( $config['is_rocket_post_excluded_option'] );
		} else {
			Functions\expect( 'is_rocket_post_excluded_option' )->with( 'async_css' )->never();
		}
	}
}
