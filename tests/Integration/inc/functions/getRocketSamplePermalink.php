<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering ::get_rocket_sample_permalink
 *
 * @group Functions
 * @group Posts
 */
class Test_GetRocketSamePermalink extends TestCase {
	private $did_filter;

	public function set_up() {
		parent::set_up();

		// Install the preload cache table to prevent DB error caused by permalink changed.
		self::installPreloadCacheTable();

		$this->did_filter = [ 'editable_slug' => 0 ];
	}

	public function tear_down() {
		// Uninstall the preload cache table.
		self::uninstallPreloadCacheTable();
		remove_filter( 'editable_slug', [ $this, 'editable_slug_cb' ] );

		parent::tear_down();
	}

	public function testShouldBailOutWhenPostDoesNotExist() {
		$this->assertSame(
			[ '', '' ],
			get_rocket_sample_permalink( - 1 )
		);

		$this->assertSame(
			[ '', '' ],
			get_rocket_sample_permalink( 0, 'Lorem ipsum' )
		);

		$this->assertEquals( 0, $this->did_filter['editable_slug'] );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnSamplePermalink( $config, $post_data, $expected ) {
		$this->set_permalink_structure( $config['structure'] );

		if ( isset( $config['parent_post'] ) ) {
			$post_data['post_parent'] = $this->factory->post->create( $config['parent_post'] );
		}
		$post_id = $this->factory->post->create( $post_data );

		add_filter( 'editable_slug', [ $this, 'editable_slug_cb' ] );
		$actual = get_rocket_sample_permalink( $post_id, $config['override_post_title'], $config['override_post_name'] );

		$this->assertSame( $expected, $actual );

		$did_filter_expected = isset( $config['parent_post'] ) ? 2 : 1;
		$this->assertEquals( $did_filter_expected, $this->did_filter['editable_slug'] );
	}

	public function editable_slug_cb( $slug ) {
		$this->did_filter['editable_slug']++;

		return $slug;
	}
}
