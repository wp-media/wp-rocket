<?php
namespace WP_Rocket\Tests\Unit\inc\classes\subscriber\Tools\Detect_Missing_Tags_Subscriber;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Subscriber\Tools\Detect_Missing_Tags_Subscriber;

/**
 * @covers \WP_Rocket\Subscriber\Tools\Detect_Missing_Tags_Subscriber::maybe_missing_tags
 * @group Subscriber
 */
class Test_MaybeMissingTags extends TestCase {
	public function setUp() : void {
		parent::setUp();
		Functions\stubTranslationFunctions();
	}

	public function testShouldIdentifyMissingHtmlAndBodyAndWPFooter() {
		$missing_tag = new Detect_Missing_Tags_Subscriber();

		$html = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/Subscriber/Tools/original_no_html_and_body.html');
		http_response_code( 200 );
		$_SERVER['content_type'] = 'text/html';

		Functions\when( 'wp_unslash' )
			->returnArg( );

		// Called did_action('wp_footer'), test also for missing wp_footer()
		Functions\expect( 'did_action' )
			->once()
			->andReturn( 0 );

		Functions\when( 'get_transient' )
			->justReturn( [] );

		Functions\when( 'get_user_meta' )
			->justReturn( [] );

		Functions\when( 'get_current_user_id' )
			->justReturn( 1 );

		Functions\expect( 'set_transient' )
			->once()
			->with('rocket_notice_missing_tags', ['</html>', '</body>', 'wp_footer()']);

		$missing_tag->maybe_missing_tags( $html );
	}

	public function testShouldNotIdentifyMissingTags() {
		$missing_tag = new Detect_Missing_Tags_Subscriber();

		$html = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/Subscriber/Tools/original_html_and_body.html');
		http_response_code( 200 );
		$_SERVER['content_type'] = 'text/html';
		Functions\when( 'wp_unslash' )
			->returnArg( );
		// Called did_action('wp_footer'), test only for HTML and BODY
		Functions\expect( 'did_action' )
			->once()
			->andReturn( true );

		Functions\expect( 'set_transient' )
			->never();

		$missing_tag->maybe_missing_tags( $html );
	}

	public function testShouldIdentifyHTMLAndBodyAreCommented(){
		$missing_tag = new Detect_Missing_Tags_Subscriber();

		$html = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/Subscriber/Tools/original_commented_html_and_body.html');
		http_response_code( 200 );
		$_SERVER['content_type'] = 'text/html';
		Functions\when( 'wp_unslash' )
			->returnArg( );
		// Called did_action('wp_footer'), test only for HTML and BODY
		Functions\expect( 'did_action' )
			->once()
			->andReturn( true );

		Functions\when( 'get_transient' )
			->justReturn( [] );

		Functions\when( 'get_user_meta' )
			->justReturn( [] );

		Functions\when( 'get_current_user_id' )
			->justReturn( 1 );

		Functions\expect( 'set_transient' )
			->once()
			->with('rocket_notice_missing_tags', ['</html>', '</body>']);

		$missing_tag->maybe_missing_tags( $html );
	}

	public function testShouldIdentifyFineHTMLAndBodyWhenAreCommented(){
		$missing_tag = new Detect_Missing_Tags_Subscriber();

		$html = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/Subscriber/Tools/original_both_html_and_body_commented.html');
		http_response_code( 200 );
		$_SERVER['content_type'] = 'text/html';
		Functions\when( 'wp_unslash' )->returnArg( );
		// Called did_action('wp_footer'), test only for HTML and BODY
		Functions\expect( 'did_action' )
			->once()
			->andReturn( true );

		Functions\expect( 'set_transient' )->never();

		$missing_tag->maybe_missing_tags( $html );
	}
}
