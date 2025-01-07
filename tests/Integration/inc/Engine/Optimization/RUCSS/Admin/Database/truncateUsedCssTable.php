<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Admin\Database;

use WP_Theme;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Admin\Database::truncate_used_css_table
 *
 * @group RUCSS
 */
class Test_TruncateUsedCssTable extends TestCase {
	public static function set_up_before_class() {
		parent::set_up_before_class();

		// Install in set_up_before_class because of exists() requiring not temporary table.
		self::installUsedCssTable();
	}

	public static function tear_down_after_class() {
		self::uninstallUsedCssTable();

		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();

		// Disable ATF optimization to prevent DB request (unrelated to the test).
		add_filter( 'rocket_above_the_fold_optimization', '__return_false' );
	}

	public function tear_down() {
		// Re-enable ATF optimization.
		remove_filter( 'rocket_above_the_fold_optimization', '__return_false' );

		remove_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );

		parent::tear_down();
	}

	public function testShouldTruncateTableWhenOptionIsEnabled() {
		$container           = apply_filters( 'rocket_container', null );
		$rucss_usedcss_query = $container->get( 'rucss_used_css_query' );

		add_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );

		$rucss_usedcss_query->add_item(
			[
				'url'            => 'http://example.org/home',
				'css'            => 'h1{color:red;}',
				'retries'        => 3,
				'is_mobile'      => false,
			]
		);
		$rucss_usedcss_query->add_item(
			[
				'url'            => 'http://example.org/home',
				'css'            => 'h1{color:red;}',
				'retries'        => 3,
				'is_mobile'      => true,
			]
		);

		$result = $rucss_usedcss_query->query(
			[],
			false
		);

		$this->assertCount( 2, $result );

		do_action( 'switch_theme', 'Test Theme', new WP_Theme( 'test', 'test' ), new WP_Theme( 'test2', 'test2' ) );

		$rucss_usedcss_query   = $container->get( 'rucss_used_css_query' );
		$result_after_truncate = $rucss_usedcss_query->query(
			[],
			false
		);

		$this->assertCount( 0, $result_after_truncate );
	}

	public function set_rucss_option() {
		return true;
	}
}
