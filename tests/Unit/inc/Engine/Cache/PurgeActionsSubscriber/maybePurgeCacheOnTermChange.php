<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\PurgeActionsSubscriber;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Logger\Logger;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Cache\Purge;
use WP_Rocket\Engine\Cache\PurgeActionsSubscriber;

/**
 * @covers \WP_Rocket\Engine\Cache\PurgeActionsSubscriber::maybe_purge_cache_on_term_change
 *
 * @group  purge_actions
 */
class Test_MaybePurgeCacheOnTermChange extends TestCase {
	private $subscriber;

	public function setUp() : void {
		parent::setUp();

		$this->subscriber = new PurgeActionsSubscriber( Mockery::mock( Options_Data::class ), Mockery::mock( Purge::class ), Mockery::mock(Logger::class) );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldPurgeCacheWhenTaxonomyPublic( $tax_name, $taxonomy, $clean ) {
		Functions\expect( 'get_taxonomy' )
			->with( $tax_name )
			->andReturn( $taxonomy );

		if ( ! $clean ) {
			Functions\expect( 'rocket_clean_domain' )->never();
		} else {
			Functions\expect( 'rocket_clean_domain' )->once();
		}

		$this->subscriber->maybe_purge_cache_on_term_change( 0, 0, $tax_name );
	}

	public function providerTestData() {
		$data = $this->getTestData( __DIR__, 'maybePurgeCacheOnTermChange' );

		return $data['unit_test_data'];
	}
}
