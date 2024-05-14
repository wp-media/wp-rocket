<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Admin\Database;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Admin\Database::truncate_used_css_table
 *
 * @group  RUCSS
 */
class Test_TruncateUsedCssTable extends TestCase{
	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );

		parent::tear_down();
	}

	public function testShouldTruncateTableWhenOptionIsEnabled(){
		$container           = apply_filters( 'rocket_container', null );
		$rucss_usedcss_table = $container->get( 'rucss_usedcss_table' );
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

		$result = $rucss_usedcss_query->query();

		$this->assertTrue( $rucss_usedcss_table->exists() );
		$this->assertCount( 2, $result );

		do_action( 'switch_theme', 'Test Theme', new \WP_Theme( 'test', 'test' ), new \WP_Theme( 'test2', 'test2' ) );

		$rucss_usedcss_query = $container->get( 'rucss_used_css_query' );
		$resultAfterTruncate = $rucss_usedcss_query->query();

		$this->assertCount( 0, $resultAfterTruncate );
	}

	public function set_rucss_option() {
		return true;
	}
}
