<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers ::get_rocket_post_dates_urls
 * @group  Posts
 * @group  Functions
 */
class Test_GetRocketPostDatesUrls extends TestCase {
	private static $user_id = 0;
	private $did_filter = 0;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$user_id = $factory->user->create(
			[
				'role'          => 'editor',
				'user_nicename' => 'rocket_tester',
			]
		);
	}

	public function setUp() {
		parent::setUp();

		wp_set_current_user( self::$user_id );
		$this->set_permalink_structure( "/%postname%/" );
		set_current_screen( 'edit.php' );
	}

	public function tearDown() {
		parent::tearDown();

		$this->did_filter = 0;
		remove_filter( 'rocket_post_dates_urls', [ $this, 'set_did_filter' ] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldGetPostDatesUrls( $post_data, $expected ) {
		$post = $this->factory->post->create_and_get( $post_data );

		add_filter( 'rocket_post_dates_urls', [ $this, 'set_did_filter' ] );

		$this->assertSame( $expected, get_rocket_post_dates_urls( $post->ID ) );
		$this->assertEquals( 1, $this->did_filter );
	}

	public function set_did_filter( $urls ) {
		$this->did_filter ++;

		return $urls;
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'getRocketPostDatesUrls' );
	}

	function get_rocket_post_dates_urls( $post_id ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
		// Get the day and month of the post.
		$date = explode( '-', get_the_time( 'Y-m-d', $post_id ) );

		$year  = trailingslashit( get_year_link( $date[0] ) );
		$month = trailingslashit( get_month_link( $date[0], $date[1] ) );

		$urls = [
			"{$year}index.html",
			"{$year}index.html_gzip",
			$year . $GLOBALS['wp_rewrite']->pagination_base,
			"{$month}index.html",
			"{$month}index.html_gzip",
			$month . $GLOBALS['wp_rewrite']->pagination_base,
			get_day_link( $date[0], $date[1], $date[2] ),
		];

		/**
		 * Filter the list of dates URLs
		 *
		 * @since 1.1.0
		 *
		 * @param array $urls List of dates URLs
		 */
		$urls = apply_filters( 'rocket_post_dates_urls', $urls );

		return $urls;
	}
}
