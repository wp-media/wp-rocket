<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\AsyncCSS;

use WP_Rocket\Engine\CriticalPath\AsyncCSS;
use WP_Rocket\Tests\Integration\FilesystemTestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase {
	protected static $container;
	protected static $use_settings_trait = true;

	protected $critical_css;
	protected $dom;
	protected $instance;
	protected $options;
	protected $default_config = [
		'options'      => [
			'async_css' => 1,
		],
		'critical_css' => [
			'get_current_page_critical_css' => 'page.css',
			'get_exclude_async_css'         => [],
		],
		'functions'    => [ 'is_rocket_post_excluded_option' => false ],
	];
	protected $test_config    = [];

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$container = apply_filters( 'rocket_container', null );
	}

	public function setUp() {
		parent::setUp();

		set_current_screen( 'front' );

		$this->options      = self::$container->get( 'options' );
		$this->critical_css = self::$container->get( 'critical_css' );
	}

	public function tearDown() {
		parent::tearDown();

		remove_filter( "pre_get_rocket_option_async_css", [ $this, 'set_option_async_css' ] );

		$this->test_config = [];
	}

	protected function setUpTest( $html, array $config = [], $should_create_instance = true ) {
		$this->test_config = $this->initConfig( $config );

		$this->mergeExistingSettingsAndUpdate( $this->test_config ['options'] );

		add_filter( "pre_get_rocket_option_async_css", [ $this, 'set_option_async_css' ] );

		$this->setUpUrl( $should_create_instance );

		$this->instance = AsyncCSS::from_html( $this->critical_css, $this->options, $html );
	}

	protected function setUpUrl( $should_create_instance ) {
		$post_id = $this->factory->post->create();
		if ( $should_create_instance ) {
			update_option( 'show_on_front', 'page' );
			$this->go_to( home_url() );
		} else {
			$this->go_to( get_permalink( $post_id ) );
		}
	}

	public function set_option_async_css( $value ) {
		if ( isset( $this->test_config['options']['async_css'] ) ) {
			return $this->test_config['options']['async_css'];
		}

		return $value;
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
}
