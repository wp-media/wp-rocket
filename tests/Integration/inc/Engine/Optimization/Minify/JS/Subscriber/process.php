<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\Minify\JS\Subscriber;

use WP_Rocket\Tests\Integration\inc\Engine\Optimization\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\Minify\JS\Subscriber::process
 * @uses   \WP_Rocket\Engine\Optimization\Minify\JS\Combine::optimize
 * @uses   \WP_Rocket\Engine\Optimization\Minify\JS\Minify::optimize
 * @uses   ::get_rocket_parse_url
 * @uses   ::get_rocket_i18n_uri
 * @uses   ::rocket_url_to_path
 * @uses   ::rocket_direct_filesystem
 * @uses   ::rocket_mkdir_p
 * @uses   ::rocket_put_content
 *
 * @group  Optimize
 * @group  MinifyJS
 * @group  Minify
 */
class Test_Process extends TestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/Minify/JS/Subscriber/process.php';

	public function tear_down() {
		parent::tear_down();

		remove_filter( 'pre_get_rocket_option_minify_js', [ $this, 'return_true' ] );
		remove_filter( 'pre_get_rocket_option_minify_js_key', [ $this, 'return_key' ] );
		remove_filter( 'pre_get_rocket_option_defer_all_js', [ $this, 'return_defer_all_js' ] );
		remove_filter( 'rocket_excluded_inline_js_content', [ $this, 'set_excluded_inline'] );
		remove_filter( 'rocket_minify_excluded_external_js', [ $this, 'set_excluded_external'] );
		delete_transient( 'wpr_dynamic_lists' );

		$this->unsetSettings();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldMinifyJS( $original, $expected, $settings ) {
		add_filter( 'pre_get_rocket_option_minify_js', [ $this, 'return_true' ] );
		add_filter( 'pre_get_rocket_option_minify_js_key', [ $this, 'return_key' ] );
		add_filter( 'rocket_excluded_inline_js_content', [ $this, 'set_excluded_inline'] );
		add_filter( 'rocket_minify_excluded_external_js', [ $this, 'set_excluded_external'] );

		set_transient( 'wpr_dynamic_lists', (object) [
			'defer_js_external_exclusions' => [
				'gist.github.com',
				'content.jwplatform.com',
				'js.hsforms.net',
				'www.uplaunch.com',
				'google.com/recaptcha',
				'widget.reviews.co.uk',
				'verify.authorize.net/anetseal',
				'lib/admin/assets/lib/webfont/webfont.min.js',
				'app.mailerlite.com',
				'widget.reviews.io',
				'simplybook.(.*)/v2/widget/widget.js',
				'/wp-includes/js/dist/i18n.min.js',
				'/wp-content/plugins/wpfront-notification-bar/js/wpfront-notification-bar(.*).js',
				'/wp-content/plugins/oxygen/component-framework/vendor/aos/aos.js',
				'/wp-content/plugins/ewww-image-optimizer/includes/check-webp(.min)?.js',
				'static.mailerlite.com/data/(.*).js',
				'cdn.voxpow.com/static/libs/v1/(.*).js',
				'cdn.voxpow.com/media/trackers/js/(.*).js',
				'use.typekit.net',
				'www.idxhome.com',
				'/wp-includes/js/dist/vendor/lodash(.min)?.js',
				'/wp-includes/js/dist/api-fetch(.min)?.js',
				'/wp-includes/js/dist/i18n(.min)?.js',
				'/wp-includes/js/dist/vendor/wp-polyfill(.min)?.js',
				'/wp-includes/js/dist/url(.min)?.js',
				'/wp-includes/js/dist/hooks(.min)?.js',
				'www.paypal.com/sdk/js',
				'js-eu1.hsforms.net',
				'yanovis.Voucher.js',
				'/carousel-upsells-and-related-product-for-woocommerce/assets/js/glide.min.js',
				'use.typekit.com',
				'/artale/modules/kirki/assets/webfont.js',
				'/api/scripts/lb_cs.js',
			],
		], HOUR_IN_SECONDS );

		$this->defer_all_js = $settings['defer_all_js'];

		add_filter( 'pre_get_rocket_option_defer_all_js', [ $this, 'return_defer_all_js' ] );

		$this->settings = $settings;
		$this->setSettings();

		$actual = apply_filters( 'rocket_buffer', $original );

		foreach ($expected['files'] as $file) {
			$file_mtime = $this->filesystem->mtime( $file );
			if ( $file_mtime ) {
				$expected['html'] = str_replace( $file."?ver={{mtime}}", $file."?ver=".$file_mtime, $expected['html'] );
			}
		}

		$this->assertSame(
			$this->format_the_html( $expected['html'] ),
			$this->format_the_html( $actual )
		);

		$this->assertFilesExists( $expected['files'] );
	}

	public function return_defer_all_js() {
		return $this->defer_all_js;
	}

	public function set_excluded_inline() {
		return [
			'nonce',
		];
	}

	public function set_excluded_external( $excluded ) {
		return array_merge( $excluded, [
			'cse.google.com/cse.js',
		] );
	}
}
