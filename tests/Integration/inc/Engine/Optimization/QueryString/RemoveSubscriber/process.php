<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\QueryString\RemoveSubscriber;

use WP_Rocket\Tests\Integration\inc\Engine\Optimization\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\QueryString\RemoveSubscriber::process
 * @uses   \WP_Rocket\Engine\Optimization\QueryString\Remove::remove_query_strings_css
 * @uses   \WP_Rocket\Engine\Optimization\QueryString\Remove::remove_query_strings_js
 * @uses   ::get_rocket_parse_url
 * @uses   ::rocket_direct_filesystem
 * @uses   ::get_rocket_i18n_uri
 * @uses   ::rocket_url_to_path
 * @uses   ::rocket_mkdir_p
 * @uses   ::rocket_put_content
 *
 * @group  RemoveQueryStrings
 */
class Test_Process extends TestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/QueryString/RemoveSubscriber/remove-query-strings.php';

	public function setUp() {
		parent::setUp();

		// Mocks constants for the virtual filesystem.
		$this->whenRocketGetConstant();
	}

	public function tearDown() {
		parent::tearDown();

		$this->unsetSettings();
		remove_filter( 'pre_get_rocket_option_remove_query_strings', [ $this, 'return_true' ] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldRemoveQueryStrings( $original, $expected, $settings ) {
		add_filter( 'pre_get_rocket_option_remove_query_strings', [ $this, 'return_true' ] );

		$this->settings = $settings;
		$this->setSettings();

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_buffer', $original )
		);
	}
}
