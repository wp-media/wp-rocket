<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Abilities\RemovePageInsights;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use WP_Rocket\Engine\Admin\RocketInsights\Abilities\RemovePageInsights;
use WP_Rocket\Engine\Admin\RocketInsights\Context\Context;
use WP_Rocket\Engine\Admin\RocketInsights\Database\Queries\RocketInsights as Query;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Abilities\RemovePageInsights::execute
 *
 * @group Admin
 * @group RocketInsights
 * @group Abilities
 */
class ExecuteTest extends TestCase {

	/**
	 * @var Context|\PHPUnit\Framework\MockObject\MockObject
	 */
	private $context;

	/**
	 * @var Query|\PHPUnit\Framework\MockObject\MockObject
	 */
	private $query;

	/**
	 * @var RemovePageInsights
	 */
	private $ability;

	protected function setUp(): void {
		parent::setUp();

		$this->context = $this->createMock( Context::class );
		$this->query   = $this->createMock( Query::class );

		$this->ability = new RemovePageInsights(
			$this->context,
			$this->query
		);

		$this->stubTranslationFunctions();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ): void {
		// Stub URL protocol addition.
		Functions\when( 'rocket_add_url_protocol' )->alias( function ( $url ) {
			if ( empty( $url ) ) {
				return $url;
			}
			if ( strpos( $url, 'http://' ) !== 0 && strpos( $url, 'https://' ) !== 0 ) {
				return 'https://' . $url;
			}
			return $url;
		} );

		// Set up Context mock.
		if ( isset( $config['context_is_allowed'] ) ) {
			$this->context
				->expects( $this->any() )
				->method( 'is_allowed' )
				->willReturn( $config['context_is_allowed'] );
		}

		// Set up Query mock.
		if ( array_key_exists( 'query_rows', $config ) ) {
			$rows = $this->build_rows( $config['query_rows'] );

			$this->query
				->expects( $this->any() )
				->method( 'get_rows_by_url' )
				->willReturn( $rows );
		}

		$deleted_ids = $expected['deleted_ids'] ?? [];

		if ( ! empty( $deleted_ids ) ) {
			// Assert delete_item() is called with each expected ID in order, and return success.
			// willReturnCallback is used over withConsecutive() for forward compatibility
			// (withConsecutive was removed in PHPUnit 10).
			$call_index = 0;

			$this->query
				->expects( $this->exactly( count( $deleted_ids ) ) )
				->method( 'delete_item' )
				->willReturnCallback( function ( $id ) use ( &$call_index, $deleted_ids ) {
					$this->assertSame( $deleted_ids[ $call_index ], $id );
					++$call_index;
					return true;
				} );

			// Removing a page fires the deletion event once, regardless of how many rows were removed.
			Actions\expectDone( 'rocket_rocket_insights_job_deleted' )->once();
		} else {
			$this->query
				->expects( $this->never() )
				->method( 'delete_item' );

			Actions\expectDone( 'rocket_rocket_insights_job_deleted' )->never();
		}

		$result = $this->ability->execute( $config['input'] ?? null );

		$this->assertSame( $expected['result'], $result );
	}

	/**
	 * Builds row objects from an array of IDs.
	 *
	 * @param mixed $rows Array of row IDs, or false/empty.
	 *
	 * @return mixed
	 */
	private function build_rows( $rows ) {
		if ( empty( $rows ) ) {
			return $rows;
		}

		return array_map( function ( $id ) {
			$row     = new \stdClass();
			$row->id = $id;
			return $row;
		}, $rows );
	}
}
