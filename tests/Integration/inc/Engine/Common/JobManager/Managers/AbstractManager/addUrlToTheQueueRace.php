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
class Test_AddUrlToTheQueueRace extends TestCase {
	private $url = 'http://example.org/sample-page';

	public function set_up() {
		parent::set_up();

		self::installUsedCssTable();

		add_filter( 'pre_get_rocket_option_remove_unused_css', '__return_true' );
	}

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_remove_unused_css', '__return_true' );

		self::uninstallUsedCssTable();

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnTheRowIdWithoutResettingTheWinner( $config, $expected ) {
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

		if ( $config['race'] ) {
			add_filter( 'query', $insert_winner );
		}

		$previous = $wpdb->suppress_errors( true );
		$result   = $manager->add_url_to_the_queue( $this->url, false );
		$wpdb->suppress_errors( $previous );

		remove_filter( 'query', $insert_winner );

		$this->assertSame( $config['race'], $raced );
		$this->assertSame( 1, (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ) );

		$row = $wpdb->get_row( "SELECT id, status, job_id FROM {$table}" );
		$this->assertSame( (int) $row->id, $result );
		$this->assertSame( $expected['status'], $row->status );
		$this->assertSame( $expected['job_id'], $row->job_id );
	}
}
