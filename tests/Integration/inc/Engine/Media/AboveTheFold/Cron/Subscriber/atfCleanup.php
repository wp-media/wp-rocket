<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\AboveTheFold\Cron\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\AboveTheFold\Cron\Subscriber::atf_cleanup
 *
 * @group AboveTheFold
 */
class Test_AtfCleanup extends TestCase {
	protected $path_to_test_data = '/inc/Engine/Media/AboveTheFold/Cron/Subscriber/atfCleanup.php';

	/**
	 * @var array
	 */
	protected $config;

	public function set_up() {
		parent::set_up();

		parent::installAtfTable();
	}

	public function tear_down() {
		parent::uninstallAtfTable();

		parent::tear_down();
	}

	/**
	 * Test should do expected.
	 *
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$this->config = $config;
		$container    = apply_filters( 'rocket_container', null );
		$current_date = current_time( 'mysql', true );
		$old_date     = strtotime( $current_date . ' - 32 days' );

		if ( ! empty( $config['rows'] ) ) {
			foreach ( $config['rows'] as $row ) {
				// If the value is 'current_date', replace it with the current date.
				if ( 'current_date' === $row['last_accessed'] ) {
					$row['last_accessed'] = $current_date;
				}

				// If the value is 'old_date', replace it with the old date.
				if ( 'old_date' === $row['last_accessed'] ) {
					$row['last_accessed'] = $old_date;
				}

				// Do the same for 'modified'.
				if ( 'current_date' === $row['modified'] ) {
					$row['modified'] = $current_date;
				}

				if ( 'old_date' === $row['modified'] ) {
					$row['modified'] = $old_date;
				}

				self::addLcp( $row );
			}
		}

		do_action( 'rocket_atf_cleanup' );

		$atf_query              = $container->get( 'atf_query' );
		$result_atf_after_clean = $atf_query->query();

		$this->assertCount( $expected['numberRowStillInDb'], $result_atf_after_clean );
	}
}
