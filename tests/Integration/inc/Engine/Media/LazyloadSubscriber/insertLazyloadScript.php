<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\LazyloadSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Media\LazyloadSubscriber::insert_lazyload_script
 * @uses   ::rocket_get_constant
 * @uses   \RocketLazyload\Assets::insertLazyloadScript
 *
 * @group  Lazyload
 */
class Test_InsertLazyloadScript extends TestCase {
	private $lazyload;
	private $iframes;
	private $threshold;

	public function setUp() {
		$this->script_debug = false;

		parent::setUp();

		$this->lazyload  = null;
		$this->iframes   = null;
		$this->threshold = null;
	}

	public function tearDown() {
		remove_filter( 'rocket_lazyload_script_tag', [ $this, 'set_js_to_min' ] );
		remove_filter( 'pre_get_rocket_option_lazyload', [ $this, 'setLazyload' ] );
		remove_filter( 'pre_get_rocket_option_lazyload_iframes', [ $this, 'setIframes' ] );
		remove_filter( 'rocket_lazyload_threshold', [ $this, 'setThreshold' ] );
		remove_filter( 'rocket_lazyload_threshold', [ $this, 'return_true' ] );
		remove_filter( 'rocket_use_native_lazyload', [ $this, 'return_true' ] );

		parent::tearDown();
	}

	private function getActualHtml() {
		ob_start();
		do_action( 'wp_footer' );

		return $this->format_the_html( ob_get_clean() );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldInsertLazyloadScript( $options, $expected ) {
		$this->lazyload = $options['lazyload'];
		$this->iframes  = $options['lazyload_iframes'];

		// wp-media/rocket-lazyload-common uses the constant for determining whether to set as .min.js.
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			add_filter( 'rocket_lazyload_script_tag', [ $this, 'set_js_to_min' ] );
		}

		add_filter( 'pre_get_rocket_option_lazyload', [ $this, 'setLazyload' ] );
		add_filter( 'pre_get_rocket_option_lazyload_iframes', [ $this, 'setIframes' ] );

		if ( isset( $options['threshold'] ) ) {
			$this->threshold = $options['threshold'];

			add_filter( 'rocket_lazyload_threshold', [ $this, 'setThreshold' ] );
		}

		if ( isset( $options['polyfill'] ) ) {
			add_filter( 'rocket_lazyload_polyfill', [ $this, 'return_true' ] );
		}

		if ( isset( $options['use_native'] ) ) {
			add_filter( 'rocket_use_native_lazyload', [ $this, 'return_true' ] );
		}

		if ( empty( $expected['integration'] ) ) {
			$this->assertNotContains(
				'http://example.org/wp-content/plugins/wp-rocket/assets/js/lazyload',
				$this->getActualHtml()
			);
		} else {
			$this->assertSame(
				$this->format_the_html( $expected['integration'] ),
				$this->getActualHtml()
			);
		}
	}

	public function setLazyload() {
		return $this->lazyload;
	}

	public function setIframes() {
		return $this->iframes;
	}

	public function setThreshold() {
		return $this->threshold;
	}

	public function set_js_to_min( $script ) {
		return str_replace( '.js', '.min.js', $script );
	}
}
