<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\LazyloadSubscriber;

use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;

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

		global $wp_query;
		$wp_query->is_feed    = false;
		$wp_query->is_preview = false;
		$wp_query->is_search  = false;

		set_current_screen('front');

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
	public function testShouldInsertLazyloadScript( $config, $expected ) {
		$options = $config['options'];
		$this->lazyload = $options['lazyload'];
		$this->iframes  = $options['lazyload_iframes'];

		$is_admin           = isset( $config['is_admin'] )           ? $config['is_admin']           : false;
		$is_feed            = isset( $config['is_feed'] )            ? $config['is_feed']            : false;
		$is_preview         = isset( $config['is_preview'] )         ? $config['is_preview']         : false;
		$is_search          = isset( $config['is_search'] )          ? $config['is_search']          : false;
		$is_rest_request    = isset( $config['is_rest_request'] )    ? $config['is_rest_request']    : false;
		$is_lazy_load       = isset( $config['is_lazy_load'] )       ? $config['is_lazy_load']       : true;
		$is_rocket_optimize = isset( $config['is_rocket_optimize'] ) ? $config['is_rocket_optimize'] : true;

		set_current_screen( $is_admin ? 'settings_page_wprocket' : 'front' );

		global $wp_query;
		$wp_query->is_feed = $is_feed;
		$wp_query->is_preview = $is_preview;
		$wp_query->is_search = $is_search;

		//Constants.
		$this->attributes['REST_REQUEST'] = $is_rest_request;
		$this->attributes['DONOTLAZYLOAD'] = !$is_lazy_load;
		$this->donotrocketoptimize = !$is_rocket_optimize;
		$this->attributes['WP_ROCKET_ASSETS_JS_URL'] = 'http://example.org/wp-content/plugins/wp-rocket/assets/';

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
