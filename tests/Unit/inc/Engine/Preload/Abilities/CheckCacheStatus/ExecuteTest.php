<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Abilities\CheckCacheStatus;

use Brain\Monkey\Functions;
use Mockery;
use stdClass;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\Abilities\CheckCacheStatus;
use WP_Rocket\Engine\Preload\Database\Queries\Cache as CacheQuery;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Tests for WP_Rocket\Engine\Preload\Abilities\CheckCacheStatus::execute()
 *
 * @group Preload
 * @group Abilities
 */
class ExecuteTest extends TestCase {
	/**
	 * Options_Data mock.
	 *
	 * @var Options_Data|Mockery\MockInterface
	 */
	private $options;

	/**
	 * CacheQuery mock.
	 *
	 * @var CacheQuery|\PHPUnit\Framework\MockObject\MockObject
	 */
	private $query;

	/**
	 * Ability instance under test.
	 *
	 * @var CheckCacheStatus
	 */
	private $ability;

	protected function setUp(): void {
		parent::setUp();

		$this->options = Mockery::mock( Options_Data::class );
		$this->query   = $this->createMock( CacheQuery::class );
		$this->ability = new CheckCacheStatus( $this->options, $this->query );

		$this->stubTranslationFunctions();
		$this->stubWpParseUrl();

		Functions\when( 'home_url' )->justReturn( 'https://example.com' );

		Functions\when( 'rocket_add_url_protocol' )->alias(
			function ( $url ) {
				if ( strpos( $url, 'http://' ) !== 0 && strpos( $url, 'https://' ) !== 0 ) {
					return 'https://' . $url;
				}
				return $url;
			}
		);

		Functions\when( 'untrailingslashit' )->alias(
			function ( $string ) {
				return rtrim( $string, '/\\' );
			}
		);

		Functions\when( 'set_url_scheme' )->alias(
			function ( $url, $scheme = null ) {
				if ( empty( $scheme ) ) {
					return $url;
				}
				return preg_replace( '#^\w+://#', $scheme . '://', $url );
			}
		);

		Functions\when( 'is_wp_error' )->justReturn( false );
	}

	public function testShouldReturnTrackedTrueWhenRowExists(): void {
		$modified_ts      = (int) strtotime( '2026-03-20T10:00:00Z' );
		$last_accessed_ts = (int) strtotime( '2026-03-21T11:00:00Z' );

		$row                = new stdClass();
		$row->status        = 'completed';
		$row->modified       = $modified_ts;
		$row->last_accessed = $last_accessed_ts;

		$this->options->shouldReceive( 'get' )
			->once()
			->with( 'manual_preload', 0 )
			->andReturn( 1 );

		$this->query->expects( $this->once() )
			->method( 'get_rows_by_url' )
			->with( 'https://example.com/page' )
			->willReturn( [ $row ] );

		$result = $this->ability->execute( [ 'url' => 'https://example.com/page' ] );

		$this->assertSame(
			[
				'resolved_url'  => 'https://example.com/page',
				'tracked'       => true,
				'status'        => 'completed',
				'modified'      => gmdate( 'Y-m-d\TH:i:s\Z', $modified_ts ),
				'last_accessed' => gmdate( 'Y-m-d\TH:i:s\Z', $last_accessed_ts ),
				'note'          => null,
				'error'         => null,
			],
			$result
		);
	}

	public function testShouldNormalizeSchemeAndWwwBeforeLookup(): void {
		$row                = new stdClass();
		$row->status        = 'pending';
		$row->modified       = 0;
		$row->last_accessed = 0;

		$this->options->shouldReceive( 'get' )
			->once()
			->with( 'manual_preload', 0 )
			->andReturn( 1 );

		// The raw input is http + www, but the site's home_url() is https + non-www.
		// The lookup must be normalized to https://example.com/page before hitting the query.
		$this->query->expects( $this->once() )
			->method( 'get_rows_by_url' )
			->with( 'https://example.com/page' )
			->willReturn( [ $row ] );

		$result = $this->ability->execute( [ 'url' => 'http://www.example.com/page' ] );

		$this->assertTrue( $result['tracked'] );
		$this->assertSame( 'pending', $result['status'] );
	}

	public function testShouldReturnNotTrackedWhenPreloadDisabled(): void {
		$this->options->shouldReceive( 'get' )
			->once()
			->with( 'manual_preload', 0 )
			->andReturn( 0 );

		$this->query->expects( $this->never() )->method( 'get_rows_by_url' );

		$result = $this->ability->execute( [ 'url' => 'https://example.com/page' ] );

		$this->assertFalse( $result['tracked'] );
		$this->assertNull( $result['status'] );
		$this->assertNull( $result['modified'] );
		$this->assertNull( $result['last_accessed'] );
		$this->assertNotEmpty( $result['note'] );
		$this->assertNull( $result['error'] );
	}

	public function testShouldReturnNotTrackedWhenNoRowFound(): void {
		$this->options->shouldReceive( 'get' )
			->once()
			->with( 'manual_preload', 0 )
			->andReturn( 1 );

		$this->query->expects( $this->once() )
			->method( 'get_rows_by_url' )
			->willReturn( false );

		$result = $this->ability->execute( [ 'url' => 'https://example.com/not-crawled-yet' ] );

		$this->assertFalse( $result['tracked'] );
		$this->assertNull( $result['error'] );
		$this->assertNotEmpty( $result['note'] );
	}

	public function testShouldResolvePostIdToPermalink(): void {
		Functions\when( 'get_permalink' )->justReturn( 'https://example.com/my-post' );

		$row                = new stdClass();
		$row->status        = 'failed';
		$row->modified       = 0;
		$row->last_accessed = 0;

		$this->options->shouldReceive( 'get' )->once()->andReturn( 1 );
		$this->query->expects( $this->once() )
			->method( 'get_rows_by_url' )
			->with( 'https://example.com/my-post' )
			->willReturn( [ $row ] );

		$result = $this->ability->execute( [ 'post_id' => 42 ] );

		$this->assertSame( 'https://example.com/my-post', $result['resolved_url'] );
		$this->assertSame( 'failed', $result['status'] );
	}

	public function testShouldReturnErrorWhenPostIdDoesNotResolve(): void {
		Functions\when( 'get_permalink' )->justReturn( false );

		$this->query->expects( $this->never() )->method( 'get_rows_by_url' );

		$result = $this->ability->execute( [ 'post_id' => 999 ] );

		$this->assertNull( $result['resolved_url'] );
		$this->assertNotEmpty( $result['error'] );
	}

	public function testShouldResolveTermIdAndTaxonomyToTermLink(): void {
		Functions\when( 'get_term_link' )->justReturn( 'https://example.com/category/news' );

		$row                = new stdClass();
		$row->status        = 'in-progress';
		$row->modified       = 0;
		$row->last_accessed = 0;

		$this->options->shouldReceive( 'get' )->once()->andReturn( 1 );
		$this->query->expects( $this->once() )
			->method( 'get_rows_by_url' )
			->with( 'https://example.com/category/news' )
			->willReturn( [ $row ] );

		$result = $this->ability->execute(
			[
				'term_id'  => 5,
				'taxonomy' => 'category',
			]
		);

		$this->assertSame( 'https://example.com/category/news', $result['resolved_url'] );
		$this->assertSame( 'in-progress', $result['status'] );
	}

	public function testShouldReturnErrorWhenTermIdSuppliedWithoutTaxonomy(): void {
		$this->query->expects( $this->never() )->method( 'get_rows_by_url' );

		$result = $this->ability->execute( [ 'term_id' => 5 ] );

		$this->assertNull( $result['resolved_url'] );
		$this->assertNotEmpty( $result['error'] );
	}

	public function testShouldReturnErrorWhenMultipleIdentifiersSupplied(): void {
		$this->query->expects( $this->never() )->method( 'get_rows_by_url' );

		$result = $this->ability->execute(
			[
				'url'     => 'https://example.com/page',
				'post_id' => 42,
			]
		);

		$this->assertNull( $result['resolved_url'] );
		$this->assertNotEmpty( $result['error'] );
	}

	public function testShouldReturnErrorWhenNoIdentifierSupplied(): void {
		$this->query->expects( $this->never() )->method( 'get_rows_by_url' );

		$result = $this->ability->execute( [] );

		$this->assertNull( $result['resolved_url'] );
		$this->assertNotEmpty( $result['error'] );
	}
}
