<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Admin\Database;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Database::truncate_used_css_table
 *
 * @group  RUCSS
 */
class Test_TruncateUsedCssTable extends TestCase{
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
		$container           = apply_filters( 'rocket_container', null );
		$rucss_usedcss_table = $container->get( 'rucss_usedcss_table' );
		$rucss_usedcss_query = $container->get( 'rucss_used_css_query' );

		add_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );

		$rucss_usedcss_query->add_item(
			[
				'url'            => 'http://example.org/home',
				'css'            => 'h1{color:red;}',
				'unprocessedcss' => wp_json_encode( [] ),
				'retries'        => 3,
				'is_mobile'      => false,
			]
		);
		$rucss_usedcss_query->add_item(
			[
				'url'            => 'http://example.org/home',
				'css'            => 'h1{color:red;}',
				'unprocessedcss' => wp_json_encode( [] ),
				'retries'        => 3,
				'is_mobile'      => true,
			]
		);

		$result = $rucss_usedcss_query->query();

		$this->assertTrue( $rucss_usedcss_table->exists() );
		$this->assertCount( 2, $result );

		do_action( 'switch_theme', 'Test Theme', new \WP_Theme( 'test', 'test' ) );

		$rucss_usedcss_query = $container->get( 'rucss_used_css_query' );
		$resultAfterTruncate = $rucss_usedcss_query->query();

		$this->assertCount( 0, $resultAfterTruncate );
	}

	public function set_rucss_option() {
		return true;
	}
}
