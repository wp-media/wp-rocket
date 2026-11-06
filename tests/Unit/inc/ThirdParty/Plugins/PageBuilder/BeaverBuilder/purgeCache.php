<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PageBuilder\BeaverBuilder;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\PageBuilder\BeaverBuilder;

/**
 * Test class covering WP_Rocket\ThirdParty\Plugins\PageBuilder\BeaverBuilder::purge_cache
 * @group BeaverBuilder
 * @group ThirdParty
 */
class Test_PurgeCache extends TestCase {
	private $beaver;

	public function setUp() : void {
		parent::setUp();

		$this->beaver = new BeaverBuilder();
	}

	public function testShouldCleanRocketCacheDirectories() {
		Functions\expect( 'rocket_clean_domain' )
			->once();
		Functions\expect( 'rocket_clean_minify' )
			->once();

		$this->beaver->purge_cache();
	}
}
