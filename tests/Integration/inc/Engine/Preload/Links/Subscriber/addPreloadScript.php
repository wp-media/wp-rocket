<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\Links\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Preload\Links\Subscriber::add_preload_script
 *
 * @group  PreloadLinks
 */
class Test_AddPreloadScript extends TestCase {
	private $preload_links;

	public function tearDown() {
		parent::tearDown();

		unset( $GLOBALS['wp'] );
		remove_filter( 'pre_get_rocket_option_preload_links', [ $this, 'set_preload_links' ] );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$GLOBALS['wp'] = (object) [
			'query_vars' => [],
			'request'    => 'http://example.org',
			'public_query_vars' => [
				'embed',
			],
		];

		if ( $config['bypass'] ) {
			$GLOBALS['wp']->query_vars['nowprocket'] = 1;
		}

		$this->preload_links = $config['options']['preload_links'];

		add_filter( 'pre_get_rocket_option_preload_links', [ $this, 'set_preload_links' ] );

		do_action( 'wp_enqueue_scripts' );

		if ( false === $expected ) {
			$this->assertFalse( wp_script_is( 'rocket-preload-links' ) );
		} else {
			$this->assertTrue( wp_script_is( 'rocket-preload-links' ) );
		}
	}

	public function set_preload_links() {
		return $this->preload_links;
	}
}
