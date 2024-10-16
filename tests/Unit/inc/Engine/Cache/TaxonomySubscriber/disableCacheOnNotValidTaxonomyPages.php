<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\TaxonomySubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\TaxonomySubscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Cache\TaxonomySubscriber::disable_cache_on_not_valid_taxonomy_pages
 *
 * @group Cache
 */
class Test_DisableCacheOnNotValidTaxonomyPages extends TestCase {
	private $subscriber;

	protected function setUp(): void {
		parent::setUp();

		$this->subscriber = new TaxonomySubscriber();
	}


	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $can_cache ) {
		Functions\expect( 'is_category' )->once()->andReturn( ! empty( $config['is_category'] ) );
		Functions\when( 'is_tag' )->justReturn( ! empty( $config['is_tag'] ) );
		Functions\when( 'is_tax' )->justReturn( ! empty( $config['is_tax'] ) );
		Functions\when( 'get_taxonomies' )->justReturn( $config['taxonomies'] ?? [] );

		if ( ! empty( $config['current_query'] ) && ! empty( $config['current_query_var'] ) ) {
			global $wp_query;
			$wp_query = (object) [
				'query_vars' => $config['current_query_var'],
				'query'      => $config['current_query'],
			];
		}

		$this->assertSame( $can_cache, $this->subscriber->disable_cache_on_not_valid_taxonomy_pages( true ) );
	}
}
