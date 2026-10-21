<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Common\JobManager\Managers\AbstractManager;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\JobManager\Managers\AbstractManager::add_url_to_the_queue
 *
 * @group RUCSS
 * @group JobManager
 */
class Test_AddUrlToTheQueue extends TestCase {
	private $url = 'http://example.org/sample-page';

	public function set_up() {
		parent::set_up();

		self::installUsedCssTable();

		add_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'enable_rucss' ] );
	}

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'enable_rucss' ] );

		self::uninstallUsedCssTable();

		parent::tear_down();
	}

	public function enable_rucss() {
		return 1;
	}

	public function testShouldReturnTheWinnerRowWithoutResettingItWhenTheInsertLosesTheRace() {
		global $wpdb;

		$manager = apply_filters( 'rocket_container', null )->get( 'rucss_manager' );
		$table   = $wpdb->prefix . 'wpr_rucss_used_css';
		$raced   = false;

		// A concurrent request inserts the row after this one found none and before its own insert runs.
		$insert_winner = function ( $sql ) use ( $wpdb, $table, &$raced ) {
			if ( $raced || 0 !== strpos( $sql, "INSERT INTO `{$table}`" ) ) {
				return $sql;
			}

			$raced = true;

			$wpdb->insert(
				$table,
				[
					'url'       => $this->url,
					'url_hash'  => md5( $this->url ),
					'is_mobile' => 0,
					'status'    => 'pending',
					'job_id'    => 'W',
				]
			);

			return $sql;
		};

		add_filter( 'query', $insert_winner );

		$previous = $wpdb->suppress_errors( true );
		$result   = $manager->add_url_to_the_queue( $this->url, false );
		$wpdb->suppress_errors( $previous );

		remove_filter( 'query', $insert_winner );

		$this->assertTrue( $raced );
		$this->assertSame( 1, (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ) );

		$row = $wpdb->get_row( "SELECT id, status, job_id FROM {$table}" );
		$this->assertSame( (int) $row->id, $result );
		$this->assertSame( 'pending', $row->status );
		$this->assertSame( 'W', $row->job_id );
	}

	public function testShouldInsertTheRowWhenNoneExists() {
		global $wpdb;

		$manager = apply_filters( 'rocket_container', null )->get( 'rucss_manager' );
		$result  = $manager->add_url_to_the_queue( $this->url, false );

		$this->assertGreaterThan( 0, $result );
		$this->assertSame( 1, (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wpr_rucss_used_css" ) );
	}
}
