<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Pressidium;

use Mockery;
use NinukisCaching;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\Pressidium::clean_post
 *
 * @group Pressidium
 */
class Test_CleanPost extends TestCase {
	protected $ninukis_caching;

	public function set_up() {
		parent::set_up();

		// Disable ATF optimization to prevent DB request (unrelated to the test).
		add_filter( 'rocket_above_the_fold_optimization', '__return_false' );

		$this->ninukis_caching = Mockery::mock( 'overload:' . NinukisCaching::class );
	}

	public function tear_down() {
		// Re-enable ATF optimization.
		remove_filter( 'rocket_above_the_fold_optimization', '__return_false' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$post = $this->factory()->post->create_and_get( $config['post'] );

		$this->ninukis_caching->shouldReceive( 'purge_url' )
			->with( $config['url'] );

		do_action( 'after_rocket_clean_post', $post, $config['url'], '' );
	}
}
