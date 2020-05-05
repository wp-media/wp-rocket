<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\LazyloadSubscriber;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Media\LazyloadSubscriber::insert_lazyload_script
 * @group  Lazyload
 */
class Test_InsertLazyloadScript extends TestCase {
	private $lazyload;
	private $iframes;

	public function tearDown() {
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
	 * @dataProvider providerTestData
	 */
	public function testShouldInsertLazyloadScript( $options, $expected ) {
		$this->lazyload = $options['lazyload'];
		$this->iframes  = $options['lazyload_iframes'];

		Functions\expect( 'rocket_get_constant' )
			->atMost()
			->times( 1 )
			->with( 'WP_ROCKET_ASSETS_JS_URL' )
			->andReturn( 'http://example.org/wp-content/plugins/wp-rocket/assets/js/' )
			->andAlsoExpectIt()
			->atMost()
			->times( 1 )
			->with( 'SCRIPT_DEBUG')
			->andReturn( false );

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

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'insertLazyloadScript' );
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
}
