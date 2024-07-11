<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * Test class covering ::get_rocket_sample_permalink
 *
 * @group Functions
 * @group Posts
 */
class Test_GetRocketSamePermalink extends TestCase {

	public function testShouldBailOutWhenPostDoesNotExist() {
		Functions\expect( 'get_post' )
			->ordered()
			->once()
			->with( - 1 )
			->andReturnNull()
			->andAlsoExpectIt()
			->once()
			->with( 0 )
			->andReturnNull();
		Functions\expect( 'get_post_type_object' )->never();

		$this->assertSame(
			[ '', '' ],
			get_rocket_sample_permalink( - 1 )
		);

		$this->assertSame(
			[ '', '' ],
			get_rocket_sample_permalink( 0, 'Lorem ipsum' )
		);
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnSamplePermalink( $config, $post_data, $expected ) {
		$post_id = 1;
		$post    = (object) array_merge(
			[
				'ID'           => $post_id,
				'post_name'    => $expected[1],
				'post_title'   => '',
				'post_content' => '',
				'post_date'    => date( 'Y-m-d' ),
				'post_status'  => 'publish',
				'post_type'    => 'post',
				'post_parent'  => isset( $config['parent_post'] ) ? $post_id + 1 : 0,
			],
			$post_data
		);

		Functions\expect( 'get_post' )
			->once()
			->with( $post_id )
			->andReturn( $post );
		Functions\expect( 'get_post_type_object' )
			->once()
			->with( $post->post_type )
			->andReturnUsing(
				function () use ( $post ) {
					return (object) [
						'name'         => $post->post_type,
						'hierarchical' => $post->post_parent > 0,
					];
				}
			);

		Functions\expect( 'sanitize_title' )
			->atLeast()
			->andReturnUsing( [ $this, 'sanitizeTitle' ] );
		Functions\expect( 'wp_unique_post_slug' )->once()->andReturnFirstArg();
		Functions\expect( 'get_permalink' )
			->once()
			->with( $post, false )
			->andReturn( $expected[0] );

		if ( isset( $config['parent_post'] ) ) {
			Functions\expect( 'get_page_uri' )
				->once()
				->with( $post )
				->andReturnUsing(
					function () use ( $expected ) {
						return str_replace( 'http://example.org/', '', $expected[0] );
					}
				);
		}

		$actual = get_rocket_sample_permalink( $post_id, $config['override_post_title'], $config['override_post_name'] );

		$this->assertSame( $expected, $actual );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'getRocketSamplePermalink' );
	}

	public function sanitizeTitle( $title ) {
		$title = str_replace( ' ', '-', strtolower( $title ) );

		return preg_replace( '/[^a-z0-9\-]/', '', $title );
	}
}
