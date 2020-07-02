<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\CriticalCSSSubscriber;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber::async_css
 * @uses   \WP_Rocket\Engine\CriticalPath\AsyncCSS::modify_html
 * @uses   \WP_Rocket\Engine\CriticalPath\AsyncCSS::from_html
 *
 * @group  Subscribers
 * @group  CriticalPath
 * @group  AsyncCSS
 * @group  abcd
 */
class Test_AsyncCss extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/CriticalCSSSubscriber/asyncCss.php';

	protected $test_config = [];
	protected $buffer_callbacks;

	public function setUp() {
		parent::setUp();

//		// Remove all of the "buffer" callbacks.
//		global $wp_filter;
//		$this->buffer_callbacks = $wp_filter['buffer_callbacks'];
//		remove_all_filters( 'buffer' );

		set_current_screen( 'front' );
	}

	public function tearDown() {
		parent::tearDown();

		remove_filter( "pre_get_rocket_option_async_css", [ $this, 'set_option_async_css' ] );
		remove_filter( 'rocket_exclude_async_css', [ $this, 'rocket_exclude_async_css' ] );

		$this->test_config = [];
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldAsyncCss( $html, $expected, $config = [] ) {
		$this->test_config = $this->initConfig( $config );
		$this->mergeExistingSettingsAndUpdate( $this->test_config ['options'] );
		add_filter( "pre_get_rocket_option_async_css", [ $this, 'set_option_async_css' ] );
		add_filter( 'rocket_exclude_async_css', [ $this, 'rocket_exclude_async_css' ] );
		$this->setUpUrl();

		// Run it.
		$actual = apply_filters( 'rocket_buffer', $html );

//		print_r ( $actual );
//		exit;

		$this->assertEquals(
			$this->format_the_html( $expected ),
			$this->format_the_html( $actual )
		);
	}

	protected function setUpUrl() {
		$post_id = $this->factory->post->create();

		if ( ! isset( $this->test_config['doesnotcreatedom'] ) || ! $this->test_config['doesnotcreatedom'] ) {
			update_option( 'show_on_front', 'page' );
			$go_to = home_url();
		} else {
			$go_to = get_permalink( $post_id );
		}

		$this->go_to( $go_to );
	}

	public function set_option_async_css( $value ) {
		if ( isset( $this->test_config['options']['async_css'] ) ) {
			return $this->test_config['options']['async_css'];
		}

		return $value;
	}

	public function rocket_exclude_async_css( $value ) {
		if ( ! isset( $this->test_config['critical_css']['get_exclude_async_css'] ) ) {
			return $value;
		}

		if ( empty( $this->test_config['critical_css']['get_exclude_async_css'] ) ) {
			return $value;
		}

		return $this->test_config['critical_css']['get_exclude_async_css'];
	}

	protected function initConfig( $config ) {
		if ( empty( $config ) ) {
			return $this->config['default_config'];
		}

		if ( isset( $config['use_default'] ) && $config['use_default'] ) {
			unset( $config['use_default'] );

			return array_merge_recursive(
				$this->config['default_config'],
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
