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
		$this->assertFileExists( WP_ROCKET_FUNCTIONS_PATH . 'options.php' );
		$this->assertFileExists( WP_ROCKET_CLASSES_PATH . 'background-processing.php' );
	}
}
