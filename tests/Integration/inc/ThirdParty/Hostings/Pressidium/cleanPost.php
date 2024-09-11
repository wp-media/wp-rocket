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

		$this->ninukis_caching = Mockery::mock( 'overload:' . NinukisCaching::class );
	}

	public function tear_down() {
		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->markTestSkipped( 'Test doest not perform assertion, need to revisit' );

		$post = $this->factory()->post->create_and_get( $config['post'] );

		$this->ninukis_caching->shouldReceive( 'purge_url' )
			->with( $config['url'] );

		do_action( 'after_rocket_clean_post', $post, $config['url'], '' );
	}
}
