<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Pressidium;

use Mockery;
use NinukisCaching;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\Pressidium::purge_url
 *
 * @group Pressidium
 */
class TestPurgeUrl extends TestCase {
	protected $ninukis_caching;

	public function set_up() {
		parent::set_up();

		$this->ninukis_caching = Mockery::mock( 'overload:' . NinukisCaching::class );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config ) {
		$this->markTestSkipped( 'Test doest not perform assertion, need to revisit' );

		$this->ninukis_caching->shouldReceive( 'purge_url' );

		do_action( 'after_rocket_clean_file' );
	}
}
