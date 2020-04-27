<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\QueryString\RemoveSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\QueryString\RemoveSubscriber::process
 * @group  RemoveQueryStrings
 */
class Test_Process extends FilesystemTestCase {
    protected $path_to_test_data = '/inc/Engine/Optimization/QueryString/RemoveSubscriber/remove-query-strings.php';
    protected $cnames;
    protected $zones;

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldRemoveQueryStrings( $original, $expected, $settings ) {
		add_filter( 'pre_get_rocket_option_remove_query_strings', [ $this, 'return_true' ] );
		add_filter( 'rocket_wp_content_dir', [ $this, 'virtual_wp_content_dir' ] );
		$this->set_settings( $settings );

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_buffer', $original )
		);

		$this->unset_settings( $settings );
		remove_filter( 'pre_get_rocket_option_remove_query_strings', [ $this, 'return_true' ] );
		remove_filter( 'rocket_wp_content_dir', [ $this, 'virtual_wp_content_dir' ] );
	}

    public function virtual_wp_content_dir() {
        return $this->filesystem->getUrl( 'wp-content' );
    }

    private function set_settings( array $settings ) {
        foreach ( $settings as $key => $value ) {

            if ( 'cdn' === $key ) {
                $callback = 0 === $value ? 'return_false' : 'return_true';
                add_filter( 'pre_get_rocket_option_cdn', [ $this, $callback ] );
                continue;
            }

            if ( 'cdn_cnames' === $key ) {
                $this->cnames = $value;
                add_filter( 'pre_get_rocket_option_cdn_cnames', [ $this, 'set_cnames'] );
                continue;
            }

            if ( 'cdn_zone' === $key ) {
                $this->zones = $value;
                add_filter( 'pre_get_rocket_option_cdn_zone', [ $this, 'set_zones'] );
                continue;
            }
        }
    }

    private function unset_settings( array $settings ) {
        foreach ( $settings as $key => $value ) {

            if ( 'cdn' === $key ) {
                $callback = 0 === $value ? 'return_false' : 'return_true';
                remove_filter( 'pre_get_rocket_option_cdn', [ $this, $callback ] );
                continue;
            }

            if ( 'cdn_cnames' === $key ) {
                remove_filter( 'pre_get_rocket_option_cdn_cnames', [ $this, 'set_cnames'] );
                continue;
            }

            if ( 'cdn_zone' === $key ) {
                remove_filter( 'pre_get_rocket_option_cdn_zone', [ $this, 'set_zones'] );
                continue;
            }
        }
    }

    public function set_cnames() {
        return $this->cnames;
    }

    public function set_zones() {
        return $this->zones;
    }
}
