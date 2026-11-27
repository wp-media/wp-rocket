<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\PreconnectExternalDomains\Frontend\Controller;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Media\PreconnectExternalDomains\Context\Context;
use WP_Rocket\Engine\Media\PreconnectExternalDomains\Database\Queries\PreconnectExternalDomains as PreconnectQuery;
use WP_Rocket\Engine\Media\PreconnectExternalDomains\Database\Row\PreconnectExternalDomains as PreconnectRow;
use WP_Rocket\Engine\Media\PreconnectExternalDomains\Frontend\Controller;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering WP_Rocket\Engine\Media\PreconnectExternalDomains\Frontend\Controller::add_preconnect_to_head
 *
 * @group PreconnectExternalDomains
 */
class Test_AddPreconnectToHead extends TestCase {
	private $wp;

	protected function setUp(): void {
		parent::setUp();

		$this->wp      = $GLOBALS['wp'] ?? null;
		$GLOBALS['wp'] = (object) [ 'request' => '' ];
	}

	protected function tearDown(): void {
		$GLOBALS['wp'] = $this->wp;

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$query   = $this->createPartialMock( PreconnectQuery::class, [ 'get_row' ] );
		$context = Mockery::mock( Context::class );

		$this->stubEscapeFunctions();
		$this->stubWpParseUrl();

		Functions\when( 'home_url' )->justReturn( 'http://example.org' );
		Functions\when( 'add_query_arg' )->justReturn( '' );
		Functions\when( 'untrailingslashit' )->alias(
			function ( $value ) {
				return rtrim( $value, '/\\' );
			}
		);
		Functions\when( 'wp_parse_args' )->alias(
			function ( $args, $defaults ) {
				return array_merge( $defaults, $args );
			}
		);

		$context->shouldReceive( 'is_allowed' )->andReturn( $config['is_allowed'] );
		$context->shouldReceive( 'is_mobile_allowed' )->andReturn( false );

		$query->method( 'get_row' )
			->with( 'http://example.org', false )
			->willReturn( null === $config['row'] ? false : new PreconnectRow( (object) $config['row'] ) );

		if ( isset( $config['use_prefetch'] ) ) {
			Filters\expectApplied( 'rocket_preconnect_external_domains_use_prefetch' )
				->andReturn( $config['use_prefetch'] );
		}

		$controller = new Controller( $query, $context );

		$this->assertSame( $expected, $controller->add_preconnect_to_head( $config['items'] ) );
	}
}
