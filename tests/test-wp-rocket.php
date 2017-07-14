<?php
class Test_WP_Rocket extends WP_UnitTestCase {
	public function test_constants() {
		$this->assertSame( WP_ROCKET_VERSION, '2.10.6' );
		$this->assertSame( WP_ROCKET_PRIVATE_KEY, false );
		$this->assertSame( WP_ROCKET_SLUG, 'wp_rocket_settings' );
		$this->assertSame( WP_ROCKET_WEB_MAIN, false );
		$this->assertSame( WP_ROCKET_WEB_API, 'api/wp-rocket/' );
		$this->assertSame( WP_ROCKET_WEB_CHECK, 'check_update.php' );
		$this->assertSame( WP_ROCKET_WEB_VALID, 'valid_key.php' );
		$this->assertSame( WP_ROCKET_WEB_INFO, 'plugin_information.php' );
		$this->assertSame( WP_ROCKET_BOT_URL, 'http://bot.wp-rocket.me/launch.php' );

		$file = str_replace( 'tests/test-', '', __FILE__ );
		$this->assertSame( WP_ROCKET_FILE, $file );

		$path = realpath( plugin_dir_path( $file ) ) . '/';
		$this->assertSame( WP_ROCKET_PATH, $path );

		$inc_path = realpath( $path . 'inc/' ) . '/';
		$this->assertSame( WP_ROCKET_INC_PATH, $inc_path );

		$front_path = realpath( $inc_path . 'front/' ) . '/';
		$this->assertSame( WP_ROCKET_FRONT_PATH, $front_path );

		$admin_path = realpath( $inc_path . 'admin' ) . '/';
		$this->assertSame( WP_ROCKET_ADMIN_PATH, $admin_path );

		$admin_ui_path = realpath( $admin_path . 'ui' ) . '/';
		$this->assertSame( WP_ROCKET_ADMIN_UI_PATH, $admin_ui_path );

		$admin_modules_path = realpath( $admin_ui_path . 'modules' ) . '/';
		$this->assertSame( WP_ROCKET_ADMIN_UI_MODULES_PATH, $admin_modules_path );

		$common_path = realpath( $inc_path . 'common' ) . '/';
		$this->assertSame( WP_ROCKET_COMMON_PATH, $common_path );

		$classes_path = realpath( $inc_path . 'classes' ) . '/';
		$this->assertSame( WP_ROCKET_CLASSES_PATH, $classes_path );

		$functions_path = realpath( $inc_path . 'functions' ) . '/';
		$this->assertSame( WP_ROCKET_FUNCTIONS_PATH, $functions_path );

		$vendors_path = realpath( $inc_path . 'vendors' ) . '/';
		$this->assertSame( WP_ROCKET_VENDORS_PATH, $vendors_path );

		$third_party_path = realpath( $inc_path . '3rd-party' ) . '/';
		$this->assertSame( WP_ROCKET_3RD_PARTY_PATH, $third_party_path );

		$this->assertSame( WP_ROCKET_CONFIG_PATH, WP_CONTENT_DIR . '/wp-rocket-config/' );
		$this->assertSame( WP_ROCKET_CACHE_PATH, WP_CONTENT_DIR . '/cache/wp-rocket/' );
		$this->assertSame( WP_ROCKET_MINIFY_CACHE_PATH, WP_CONTENT_DIR . '/cache/min/' );
		$this->assertSame( WP_ROCKET_CACHE_BUSTING_PATH, WP_CONTENT_DIR . '/cache/busting/' );

		$url = plugin_dir_url( $file );
		$this->assertSame( WP_ROCKET_URL, $url );

		$inc_url = $url . 'inc/';
		$this->assertSame( WP_ROCKET_INC_URL, $inc_url );

		$front_url = $inc_url . 'front/';
		$this->assertSame( WP_ROCKET_FRONT_URL, $front_url );

		$front_js_url = $front_url . 'js/';
		$this->assertSame( WP_ROCKET_FRONT_JS_URL, $front_js_url );

		$this->assertSame( WP_ROCKET_LAZYLOAD_JS_VERSION, '1.0.5' );

		$admin_url = $inc_url . 'admin/';
		$this->assertSame( WP_ROCKET_ADMIN_URL, $admin_url );

		$admin_ui_url = $admin_url . 'ui/';
		$this->assertSame( WP_ROCKET_ADMIN_UI_URL, $admin_ui_url );

		$admin_ui_js_url = $admin_ui_url . 'js/';
		$this->assertSame( WP_ROCKET_ADMIN_UI_JS_URL, $admin_ui_js_url );

		$admin_ui_css_url = $admin_ui_url . 'css/';
		$this->assertSame( WP_ROCKET_ADMIN_UI_CSS_URL, $admin_ui_css_url );

		$admin_ui_img_url = $admin_ui_url . 'img/';
		$this->assertSame( WP_ROCKET_ADMIN_UI_IMG_URL, $admin_ui_img_url );

		$this->assertSame( WP_ROCKET_CACHE_URL, WP_CONTENT_URL . '/cache/wp-rocket/' );
		$this->assertSame( WP_ROCKET_MINIFY_CACHE_URL, WP_CONTENT_URL . '/cache/min/' );
		$this->assertSame( WP_ROCKET_CACHE_BUSTING_URL, WP_CONTENT_URL . '/cache/busting/' );

		$this->assertSame( CHMOD_WP_ROCKET_CACHE_DIRS, 0755 );
		$this->assertSame( WP_ROCKET_LASTVERSION, '2.9.11' );
	}

	public function test_requires() {
		$this->assertFileExists( WP_ROCKET_CLASSES_PATH . 'background-processing.php' );

		$this->assertFileExists( WP_ROCKET_ADMIN_PATH . 'ajax.php' );
		$this->assertFileExists( WP_ROCKET_ADMIN_PATH . 'upgrader.php' );
		$this->assertFileExists( WP_ROCKET_ADMIN_PATH . 'updater.php' );
		$this->assertFileExists( WP_ROCKET_ADMIN_PATH . 'class-repeater-field.php' );
		$this->assertFileExists( WP_ROCKET_ADMIN_PATH . 'options.php' );
		$this->assertFileExists( WP_ROCKET_ADMIN_PATH . 'admin.php' );
		$this->assertFileExists( WP_ROCKET_ADMIN_PATH . 'plugin-compatibility.php' );

		$this->assertFileExists( WP_ROCKET_ADMIN_UI_PATH . 'enqueue.php' );
		$this->assertFileExists( WP_ROCKET_ADMIN_UI_PATH . 'notices.php' );
		$this->assertFileExists( WP_ROCKET_ADMIN_UI_PATH . 'meta-boxes.php' );

		$this->assertFileExists( WP_ROCKET_FUNCTIONS_PATH . 'options.php' );
		$this->assertFileExists( WP_ROCKET_FUNCTIONS_PATH . 'files.php' );
		$this->assertFileExists( WP_ROCKET_FUNCTIONS_PATH . 'posts.php' );
		$this->assertFileExists( WP_ROCKET_FUNCTIONS_PATH . 'admin.php' );
		$this->assertFileExists( WP_ROCKET_FUNCTIONS_PATH . 'formatting.php' );
		$this->assertFileExists( WP_ROCKET_FUNCTIONS_PATH . 'cdn.php' );
		$this->assertFileExists( WP_ROCKET_FUNCTIONS_PATH . 'minify.php' );
		$this->assertFileExists( WP_ROCKET_FUNCTIONS_PATH . 'plugins.php' );
		$this->assertFileExists( WP_ROCKET_FUNCTIONS_PATH . 'i18n.php' );
		$this->assertFileExists( WP_ROCKET_FUNCTIONS_PATH . 'bots.php' );
		$this->assertFileExists( WP_ROCKET_FUNCTIONS_PATH . 'htaccess.php' );
		$this->assertFileExists( WP_ROCKET_FUNCTIONS_PATH . 'varnish.php' );

		$this->assertFileExists( WP_ROCKET_INC_PATH . 'deprecated.php' );
		$this->assertFileExists( WP_ROCKET_INC_PATH . 'domain-mapping.php' );

		$this->assertFileExists( WP_ROCKET_FRONT_PATH . 'plugin-compatibility.php' );
		$this->assertFileExists( WP_ROCKET_FRONT_PATH . 'theme-compatibility.php' );
		$this->assertFileExists( WP_ROCKET_FRONT_PATH . 'cdn.php' );
		$this->assertFileExists( WP_ROCKET_FRONT_PATH . 'minify.php' );
		$this->assertFileExists( WP_ROCKET_FRONT_PATH . 'cookie.php' );
		$this->assertFileExists( WP_ROCKET_FRONT_PATH . 'images.php' );
		$this->assertFileExists( WP_ROCKET_FRONT_PATH . 'enqueue.php' );
		$this->assertFileExists( WP_ROCKET_FRONT_PATH . 'dns-prefetch.php' );
		$this->assertFileExists( WP_ROCKET_FRONT_PATH . 'deferred-js.php' );
		$this->assertFileExists( WP_ROCKET_FRONT_PATH . 'async-css.php' );
		$this->assertFileExists( WP_ROCKET_FRONT_PATH . 'lazyload.php' );
		$this->assertFileExists( WP_ROCKET_FRONT_PATH . 'protocol.php' );

		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . '3rd-party.php' );

		$this->assertFileExists( WP_ROCKET_COMMON_PATH . 'admin-bar.php' );
		$this->assertFileExists( WP_ROCKET_COMMON_PATH . 'updater.php' );
		$this->assertFileExists( WP_ROCKET_COMMON_PATH . 'emoji.php' );
		$this->assertFileExists( WP_ROCKET_COMMON_PATH . 'embeds.php' );
		$this->assertFileExists( WP_ROCKET_COMMON_PATH . 'purge.php' );
		$this->assertFileExists( WP_ROCKET_COMMON_PATH . 'cron.php' );

		if ( phpversion() >= '5.4' ) {
			$this->assertFileExists( WP_ROCKET_VENDORS_PATH . 'CloudFlare/Exception/AuthenticationException.php' );
			$this->assertFileExists( WP_ROCKET_VENDORS_PATH . 'CloudFlare/Exception/UnauthorizedException.php' );
			$this->assertFileExists( WP_ROCKET_VENDORS_PATH . 'CloudFlare/Api.php' );
			$this->assertFileExists( WP_ROCKET_VENDORS_PATH . 'CloudFlare/IPs.php' );
			$this->assertFileExists( WP_ROCKET_VENDORS_PATH . 'CloudFlare/Zone.php' );
			$this->assertFileExists( WP_ROCKET_VENDORS_PATH . 'CloudFlare/Zone/Cache.php' );
			$this->assertFileExists( WP_ROCKET_VENDORS_PATH . 'CloudFlare/Zone/Settings.php' );
			$this->assertFileExists( WP_ROCKET_VENDORS_PATH . 'ip_in_range.php' );
			$this->assertFileExists( WP_ROCKET_COMMON_PATH . 'cloudflare.php' );
			$this->assertFileExists( WP_ROCKET_FUNCTIONS_PATH . 'cloudflare.php' );
		}

		$file = str_replace( 'tests/test-', '', __FILE__ );
		$this->assertFileExists( dirname( $file ) . '/licence-data.php' );

		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'hosting/wpengine.php' );
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'hosting/flywheel.php' );
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'hosting/wp-serveur.php' );
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'hosting/varnish.php' );
		
		if ( version_compare( phpversion(), '5.3.0', '>=' ) ) {
			$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'hosting/savvii.php' );
			$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'hosting/godaddy.php' );
			$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'plugins/geotargetingwp.php' );
		}
		
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'slider/revslider.php' );
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'i18n/wpml.php' );
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'i18n/polylang.php' );
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'ecommerce/woocommerce.php' );
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'ecommerce/aelia-currencyswitcher.php' );
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'ecommerce/aelia-prices-by-country.php' );
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'ecommerce/aelia-tax-display-by-country.php' );
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'ecommerce/woocommerce-multilingual.php' );
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'ecommerce/woocommerce-currency-converter-widget.php' );
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'ecommerce/edd-software-licencing.php' );
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'age-verify.php' );
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'autoptimize.php' );
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'eu-cookie-law.php' );
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'weepie-cookie-allow.php' );
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'kk-star-ratings.php' );
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'wp-postratings.php' );
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'wp-print.php' );
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'buddypress.php' );
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'disqus.php' );
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'give.php' );
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'custom-login.php' );
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'mobile/amp.php' );
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'mobile/wp-appkit.php' );
		//require( WP_ROCKET_3RD_PARTY_PATH . 'jetpack.php' );
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'yoast-seo.php' );
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'all-in-one-seo-pack.php' );
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'wp-rest-api.php' );
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'page-builder/beaver-builder.php' );
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'page-builder/thrive-visual-editor.php' );
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'page-builder/visual-composer.php' );
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'security/secupress.php' );
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'simple-custom-css.php' );
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'wp-retina-2x.php' );
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'plugins/sf-move-login.php' );
		
		$this->assertFileExists( WP_ROCKET_3RD_PARTY_PATH . 'themes/divi.php' );
	}
}
