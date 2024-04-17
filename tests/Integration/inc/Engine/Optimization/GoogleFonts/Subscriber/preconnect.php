<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\GoogleFonts\Subscriber;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\GoogleFonts\Subscriber::preconnect
 * @group CombineGoogleFonts
 */
class Test_Preconnect extends TestCase {
	private $option_value;
	private $cache_logged_user;

	public function set_up() {
		parent::set_up();

		$this->option_value = null;
	}

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_minify_google_fonts', [ $this, 'set_option' ] );

		unset( $GLOBALS['wp'] );

		parent::tear_down();
	}

    /**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpectedArray( $bypass, $option_value, $urls, $relation_type, $user_logged_in, $cache_logged_user, $expected ) {
		Functions\when( 'is_user_logged_in' )->justReturn( $user_logged_in );

		if ( $bypass ) {
			$_GET['nowprocket'] = 1;
		}

		$this->option_value = $option_value;
		$this->cache_logged_user = $cache_logged_user;

		add_filter( 'pre_get_rocket_option_minify_google_fonts', [ $this, 'set_option' ] );
		add_filter( 'pre_get_rocket_option_cache_logged_user', [ $this, 'set_cache_logged_user' ] );

		$this->assertSame(
			$expected,
			apply_filters( 'wp_resource_hints', $urls, $relation_type )
        );   
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'preconnect' );
	}

	public function set_option() {
		return $this->option_value;
	}

	public function set_cache_logged_user() {
		return $this->cache_logged_user;
	}
}