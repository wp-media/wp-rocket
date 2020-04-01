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

	public function testShouldBailOutWhenPostDoesNotExist() {
		$this->assertNull( get_post( -1 ) );
		$this->assertSame( [], get_rocket_post_dates_urls( -1 ) );
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
		$this->did_filter++;

		return $urls;
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'getRocketPostDatesUrls' );
	}
}
