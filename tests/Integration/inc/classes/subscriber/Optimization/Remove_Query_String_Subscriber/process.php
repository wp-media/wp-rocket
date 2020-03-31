<?php

namespace WP_Rocket\Tests\Integration\inc\classes\subscriber\Optimization\Remove_Query_String_Subscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Subscriber\Optimization\Remove_Query_String_Subscriber::process
 * @group  RemoveQueryString
 */
class Test_Process extends FilesystemTestCase {
    protected $path_to_test_data = '/inc/classes/subscriber/Optimization/Remove_Query_String_Subscriber/remove-query-strings.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldRemoveQueryStrings( $original, $expected ) {
        add_filter( 'pre_get_rocket_option_remove_query_strings', [ $this, 'return_true' ] );
        add_filter( 'rocket_wp_content_dir', [ $this, 'virtual_wp_content_dir' ] );

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_buffer', $original )
		);

        remove_filter( 'pre_get_rocket_option_remove_query_strings', [ $this, 'return_true' ] );
        remove_filter( 'rocket_wp_content_dir', [ $this, 'virtual_wp_content_dir' ] );
    }

    public function virtual_wp_content_dir() {
        return $this->filesystem->getUrl( 'wp-content' );
    }
}
