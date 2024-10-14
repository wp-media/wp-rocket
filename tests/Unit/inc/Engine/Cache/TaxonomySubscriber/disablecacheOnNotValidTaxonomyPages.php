<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\TaxonomySubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\Cache\Config\ConfigSubscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Cache\TaxonomySubscriber::disable_cache_on_not_valid_taxonomy_pages
 *
 * @group Cache
 */
class Test_DisableCacheOnNotValidTaxonomyPages extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {

	}
}
