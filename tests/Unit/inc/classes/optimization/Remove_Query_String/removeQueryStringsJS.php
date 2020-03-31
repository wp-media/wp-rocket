<?php

namespace WP_Rocket\Tests\Unit\inc\optimization\Remove_Query_String;

use Brain\Monkey\Filters;

/**
 * @covers \WP_Rocket\Optimization\Remove_Query_String::remove_query_strings_js
 * @group  Optimize
 * @group  RemoveQueryStrings
 */
class Test_RemoveQueryStringsJS extends TestCase {
    protected $path_to_test_data = '/inc/classes/optimization/Remove_Query_String/js/remove-query-strings.php';

    /**
     * @dataProvider providerTestData
     */
    public function testShouldRemoveQueryStringsWhenJSURL( $original, $expected, $cdn_host, $cdn_url, $site_url ) {
        Filters\expectApplied( 'rocket_cdn_hosts' )
			->zeroOrMoreTimes()
			->with( [], [ 'all', 'css_and_js', 'css', 'js' ] )
            ->andReturn( $cdn_host );

        Filters\expectApplied( 'rocket_before_url_to_path' )
			->zeroOrMoreTimes()
			->andReturnUsing( function( $url ) use ( $cdn_url, $site_url ) {
                return str_replace( $cdn_url, $site_url, $url );
            } );

        Filters\expectApplied( 'rocket_js_url' )
            ->zeroOrMoreTimes()
            ->andReturnUsing( function( $url, $original_url ) use ( $cdn_url ) {
                return str_replace( 'http://example.org', $cdn_url, $url );
            } );

        $this->assertSame(
            $expected,
            $this->rqs->remove_query_strings_js( $original )
        );
    }
}
