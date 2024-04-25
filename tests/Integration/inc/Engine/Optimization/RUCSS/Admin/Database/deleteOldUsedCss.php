<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Admin\Database;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Admin\Database::delete_old_used_css
 *
 * @group  RUCSS
 */
class Test_DeleteOldUsedCss extends TestCase{
	use DBTrait;

	public static function set_up_before_class() {
		self::installFresh();

		parent::set_up_before_class();
	}

	public static function tear_down_after_class() {
		parent::tear_down_after_class();

		self::uninstallAll();
	}

	public function tear_down() : void {
		remove_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );

		parent::tear_down();
	}

	public function testShouldTruncateTableWhenOptionIsEnabled(){
		$container           = apply_filters( 'rocket_container', null );
		$rucss_usedcss_table = $container->get( 'rucss_usedcss_table' );
		$rucss_usedcss_query = $container->get( 'rucss_used_css_query' );

		add_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );
		$current_date = current_time( 'mysql', true );
		$old_date     = date('Y-m-d H:i:s', strtotime( $current_date. ' - 32 days' ) );

		$rucss_usedcss_query->add_item(
			[
				'url'            => 'http://example.org/home',
				'css'            => 'h1{color:red;}',
				'retries'        => 3,
				'is_mobile'      => false,
				'last_accessed'  => $current_date,
			]
		);
		$rucss_usedcss_query->add_item(
			[
				'url'            => 'http://example.org/home',
				'css'            => 'h1{color:red;}',
				'retries'        => 3,
				'is_mobile'      => true,
				'last_accessed'  => $old_date,
			]
		);

		$result = $rucss_usedcss_query->query();

		$this->assertTrue( $rucss_usedcss_table->exists() );
		$this->assertCount( 2, $result );

		do_action( 'rocket_saas_clean_rows_time_event' );

		$rucss_usedcss_query = $container->get( 'rucss_used_css_query' );
		$resultAfterTruncate = $rucss_usedcss_query->query();

		$this->assertCount( 1, $resultAfterTruncate );
	}

	public function set_rucss_option() {
		return true;
	}
}
