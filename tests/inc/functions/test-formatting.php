<?php
class WP_Rocket_Formatting_Test extends WP_UnitTestCase {
	function test_rocket_clean_exclude_file() {
		$path = rocket_clean_exclude_file( 'http://www.geekpress.fr/referencement-wordpress/' );
		$path_with_query_string = rocket_clean_exclude_file( 'http://www.geekpress.fr/referencement-wordpress/?test=toto' );
		$path_without_scheme = rocket_clean_exclude_file( '//www.geekpress.fr/referencement-wordpress/?test=toto' );

		$this->assertEquals( '/referencement-wordpress/', $path );
		$this->assertEquals( '/referencement-wordpress/', $path_with_query_string );
		$this->assertEquals( '/referencement-wordpress/', $path_without_scheme );
	}

	function test_rocket_sanitize_css() {
		$file_with_css_extension 	= rocket_sanitize_css( 'test.css' );
		$file_without_css_extension = rocket_sanitize_css( 'test.js' );

		$this->assertEquals( 'test.css', $file_with_css_extension );
		$this->assertFalse( $file_without_css_extension );
	}

	function test_rocket_sanitize_js() {
		$file_with_js_extension    = rocket_sanitize_js( 'test.js' );
		$file_without_js_extension = rocket_sanitize_js( 'test.xml' );

		$this->assertEquals( 'test.js', $file_with_js_extension );
		$this->assertFalse( $file_without_js_extension );
	}

	function test_rocket_sanitize_xml() {
		$file_with_xml_extension 	= rocket_sanitize_xml( 'test.xml' );
		$file_without_xml_extension = rocket_sanitize_xml( 'test.css' );

		$this->assertEquals( 'test.xml', $file_with_xml_extension );
		$this->assertFalse( $file_without_xml_extension );
	}

	function test_rocket_sanitize_key() {
		$special_chars = rocket_sanitize_key( 'abcd$.3' );
		$uppercase = rocket_sanitize_key( 'WP_ROCKET-2.9' );
		
		$this->assertEquals( 'abcd3', $special_chars );
		$this->assertEquals( 'WP_ROCKET-29', $uppercase );
	}

	function test_rocket_sanitize_ua() {
		$bad_user_agent = rocket_sanitize_ua( '&iPhone$' );
		$user_agent_regex = rocket_sanitize_ua( '(.*)iPhone 4_S\/Apple-phone.4' );

		$this->assertEquals( 'iPhone', $bad_user_agent );
		$this->assertEquals( '(.*)iPhone 4_S\/Apple-phone.4', $user_agent_regex );
	}

	function test_rocket_remove_url_protocol() {
		$http_url_without_protocol  = rocket_remove_url_protocol( 'http://wordpress.dev' );
		$https_url_without_protocol = rocket_remove_url_protocol( 'https://wordpress.dev' );

		$this->assertEquals( 'wordpress.dev', $http_url_without_protocol );
		$this->assertEquals( 'wordpress.dev', $https_url_without_protocol );

		$http_url_without_protocol_nodots  = rocket_remove_url_protocol( 'http://wordpress.dev', true );
		$https_url_without_protocol_nodots = rocket_remove_url_protocol( 'https://wordpress.dev', true );

		$this->assertEquals( 'wordpress_dev', $http_url_without_protocol_nodots );
		$this->assertEquals( 'wordpress_dev', $https_url_without_protocol_nodots );
	}

	function test_rocket_add_url_protocol() {
		$url_with_protocol_doubleslash = rocket_add_url_protocol( '//wordpress.dev' );
		$url_with_protocol			   = rocket_add_url_protocol( 'wordpress.dev' );

		$this->assertEquals( 'http://wordpress.dev', $url_with_protocol_doubleslash );
		$this->assertEquals( 'http://wordpress.dev', $url_with_protocol );
	}

	function test_rocket_set_internal_url_scheme() {
		$url_with_scheme = rocket_set_internal_url_scheme( home_url( '/test.css' ) );
		$external_url    = rocket_set_internal_url_scheme( 'http://google.fr/test.php' );

		$this->assertEquals( home_url( '/test.css' ), $url_with_scheme );
		$this->assertEquals( 'http://google.fr/test.php', $external_url );
	}

	function test_rocket_get_domain() {
		$base_domain     = rocket_get_domain( 'http://sub.wordpress.dev' );
		$base_domain_doubleslash     = rocket_get_domain( '//sub.wordpress.dev' );
		$base_domain_double_subdomain    = rocket_get_domain( 'sub.sub.wordpress.dev' );
		$incorrect_value = rocket_get_domain( 'abcdef' );

		$this->assertEquals( 'wordpress.dev', $base_domain );
		$this->assertEquals( 'wordpress.dev', $base_domain_doubleslash );
		$this->assertEquals( 'wordpress.dev', $base_domain_double_subdomain );
		$this->assertFalse( $incorrect_value );
	}

	function test_get_rocket_parse_url() {
		$parsed_url             = get_rocket_parse_url( 'http://wordpress.dev/test/?query=toto' );
		$expected_parsed_values = array( 
			'host'   => 'wordpress.dev',
			'path'   => '/test/',
			'scheme' => 'http',
			'query'  => 'query=toto',
		);

		$not_string             = get_rocket_parse_url( 123 );

		$this->assertEquals( $expected_parsed_values, $parsed_url );
		$this->assertNull( $not_string );
	}

	function test_rocket_get_cache_busting_paths() {
		$cache_busting_paths = rocket_get_cache_busting_paths( 'wp-content-plugins-wp-rocket-js-lazyload-1.0.5.min.js', 'js' );
		$expected_cache_busting_paths = array(
			'bustingpath' => WP_CONTENT_DIR . '/cache/busting/1',
			'filepath'	  => WP_CONTENT_DIR . '/cache/busting/1/wp-content-plugins-wp-rocket-js-lazyload-1.0.5.min.js',
			'url'		  => WP_CONTENT_URL . '/cache/busting/1/wp-content-plugins-wp-rocket-js-lazyload-1.0.5.min.js',
		);

		$this->assertEquals( $expected_cache_busting_paths, $cache_busting_paths );
	}

	function test_rocket_realpath() {
		$relative_path = rocket_realpath( '/wp-content/plugins/plugin-test/inc/../assets/js/javascript.js', false );
		$absolute_path = rocket_realpath( 'http://example.org/wp-content/plugins/plugin-test/inc/../assets/js/javascript.js' );

		$expected_relative_path = '/wp-content/plugins/plugin-test/assets/js/javascript.js';
		$expected_absolute_path = ABSPATH . 'wp-content/plugins/plugin-test/assets/js/javascript.js';

		$this->assertEquals( $expected_relative_path, $relative_path );
		$this->assertEquals( $expected_absolute_path, $absolute_path );
	}
}