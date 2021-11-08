<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Admin\Database;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Database::delete_old_resources
 *
 * @group  RUCSS
 */
class Test_DeleteOldResources extends TestCase{
	use DBTrait;

	public static function setUpBeforeClass(): void {
		self::installFresh();

		parent::setUpBeforeClass();
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::uninstallAll();
	}

	public function tearDown() : void {
		remove_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );

		parent::tearDown();
	}

	public function testShouldTruncateTableWhenOptionIsEnabled(){
		$container             = apply_filters( 'rocket_container', null );
		$rucss_resources_table = $container->get( 'rucss_resources_table' );
		$rucss_resources_query = $container->get( 'rucss_resources_query' );

		add_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );
		$current_date = current_time( 'mysql', true );
		$old_date     = date('Y-m-d H:i:s', strtotime( $current_date. ' - 32 days' ) );

		$rucss_resources_query->add_item(
			[
				'url'           => 'http://example.org/path/to/file1.css',
				'type'          => 'css',
				'media'         => '',
				'content'       => 'h1{color:red;}',
				'hash'          => 'hash1',
				'modified'      => $current_date,
				'last_accessed' => $current_date,
			]
		);
		$rucss_resources_query->add_item(
			[
				'url'           => 'http://example.org/path/to/file2.css',
				'type'          => 'css',
				'media'         => '',
				'content'       => 'h1{color:red;}',
				'hash'          => 'hash1',
				'modified'      => $old_date,
				'last_accessed' => $old_date,
			]
		);

		$result = $rucss_resources_query->query();

		$this->assertTrue( $rucss_resources_table->exists() );
		$this->assertCount( 2, $result );

		do_action( 'rocket_rucss_clean_rows_time_event' );

		$rucss_resources_query = $container->get( 'rucss_resources_query' );
		$resultAfterDeleteOld  = $rucss_resources_query->query();

		$this->assertCount( 1, $resultAfterDeleteOld );
	}

	public function set_rucss_option() {
		return true;
	}
}
