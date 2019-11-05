<?php
namespace WP_Rocket\Tests\Unit\Subscriber\Tools;

use WP_Rocket\Subscriber\Tools\Detect_Missing_Tags_Subscriber;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

class TestDetectMissingTags extends TestCase {

	use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

	protected function setUp() {
		parent::setUp();

		$this->mockCommonWpFunctions();

		if ( ! defined('HOUR_IN_SECONDS') ) {
			define('HOUR_IN_SECONDS', 60 * 60);
		}
	}

	/**
	 * Test Detect_Missing_Tags_Subscriber->maybe_missing_tags() with missing HTML code </html> and </body> are missing.
	 */
	public function testShouldIdentifyMissingHtmlAndBodyAndWPFooter() {
		$missing_tag = new Detect_Missing_Tags_Subscriber();

		$html = \file_get_contents( WP_ROCKET_PLUGIN_TESTS_ROOT . '/../Fixtures/Subscriber/Tools/original_no_html_and_body.html');

		// Called did_action('wp_footer'), test also for missing wp_footer()
		Functions\expect( 'did_action' )
			->once()
			->andReturn( 0 );

		Functions\expect( 'get_transient' )
			->once()
			->andReturn( [] );

		Functions\expect( 'get_user_meta' )
			->once()
			->andReturn( [] );

		Functions\expect( 'get_current_user_id' )
			->once()
			->andReturn( 1 );

		Functions\expect( 'set_transient' )
			->once()
			->with('rocket_notice_missing_tags', ['</html>', '</body>', 'wp_footer()'], HOUR_IN_SECONDS);

		$missing_tag->maybe_missing_tags( $html );
	}

	/**
	 * Test should identify all elements
	 */
	public function testShouldNotIdentifyMissingTags() {
		$missing_tag = new Detect_Missing_Tags_Subscriber();

		$html = \file_get_contents( WP_ROCKET_PLUGIN_TESTS_ROOT . '/../Fixtures/Subscriber/Tools/original_html_and_body.html');

		// Called did_action('wp_footer'), test only for HTML and BODY
		Functions\expect( 'did_action' )
			->once()
			->andReturn( true );

		Functions\expect( 'set_transient' )
			->never();

		$missing_tag->maybe_missing_tags( $html );
	}


	/**
	 * Test should identify that </html> and </body> are commented
	 */
	public function testShouldIdentifyHTMLAndBodyAreCommented(){
		$missing_tag = new Detect_Missing_Tags_Subscriber();

		$html = \file_get_contents( WP_ROCKET_PLUGIN_TESTS_ROOT . '/../Fixtures/Subscriber/Tools/original_commented_html_and_body.html');

		// Called did_action('wp_footer'), test only for HTML and BODY
		Functions\expect( 'did_action' )
			->once()
			->andReturn( true );

		Functions\expect( 'get_transient' )
			->once()
			->andReturn( [] );

		Functions\expect( 'get_user_meta' )
			->once()
			->andReturn( [] );

		Functions\expect( 'get_current_user_id' )
			->once()
			->andReturn( 1 );

		Functions\expect( 'set_transient' )
			->once()
			->with('rocket_notice_missing_tags', ['</html>', '</body>'], HOUR_IN_SECONDS);

		$missing_tag->maybe_missing_tags( $html );
	}

	/**
	 * Test should identify that <html> and </body> are fine when are available and also commented
	 */
	public function testShouldIdentifyFineHTMLAndBodyWhenAreCommented(){
		$missing_tag = new Detect_Missing_Tags_Subscriber();

		$html = \file_get_contents( WP_ROCKET_PLUGIN_TESTS_ROOT . '/../Fixtures/Subscriber/Tools/original_both_html_and_body_commented.html');

		// Called did_action('wp_footer'), test only for HTML and BODY
		Functions\expect( 'did_action' )
			->once()
			->andReturn( true );

		Functions\expect( 'set_transient' )
			->never();

		$missing_tag->maybe_missing_tags( $html );
	}
}
