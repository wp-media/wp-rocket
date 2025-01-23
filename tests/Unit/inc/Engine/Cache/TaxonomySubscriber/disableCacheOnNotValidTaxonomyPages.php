<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\TaxonomySubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\TaxonomySubscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Cache\TaxonomySubscriber::disable_cache_on_not_valid_taxonomy_pages
 *
 * @uses \WP_Rocket\Engine\Cache\TaxonomySubscriber::is_not_valid_taxonomies_page
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
		Functions\when( 'get_queried_object_id' )->justReturn( $config['current_term_id'] ?? 0 );

		Functions\when( 'get_term_link' )->justReturn( $config['current_term_link'] ?? '' );
		Functions\when( 'add_query_arg' )->justReturn( '' );
		Functions\when( 'home_url' )->justReturn( $config['current_page_url'] ?? '' );
		Functions\when( 'is_paged' )->justReturn( ! empty( $config['page'] ) );
		Functions\when( 'get_query_var' )->justReturn( $config['page'] ?? 0 );

		$this->assertSame( $can_cache, $this->subscriber->disable_cache_on_not_valid_taxonomy_pages( true ) );
	}
}
