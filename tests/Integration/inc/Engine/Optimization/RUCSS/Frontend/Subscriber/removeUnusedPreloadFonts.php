<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Frontend\Subscriber;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Frontend\Subscriber::remove_unused_preload_fonts
 *
 * @group RUCSS
 * @group removefonts
 */
class Test_RemoveUnusedPreloadFonts extends TestCase {
	use DBTrait;

	public static function setUpBeforeClass(): void {
		self::installFresh();

		parent::setUpBeforeClass();
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::uninstallAll();
	}

	public function tearDown() {
		remove_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );
		remove_filter( 'pre_option_wp_rocket_prewarmup_stats', [ $this, 'return_prewarmup_stats' ] );

		self::truncateUsedCssTable();

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $fonts, $expected ) {
		$this->donotrocketoptimize = $config['donotoptimize'];

		add_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );
		add_filter( 'pre_option_wp_rocket_prewarmup_stats', [ $this, 'return_prewarmup_stats' ] );

		if ( false !== $config['query'] ) {
			$container = apply_filters( 'rocket_container', null );

			$rucss_usedcss_query = $container->get( 'rucss_used_css_query' );

			$rucss_usedcss_query->add_item( $config['query'] );
		}

		$this->assertSame(
			array_values( $expected ),
			array_values( apply_filters( 'rocket_preload_fonts', $fonts ) )
		);
	}

	public function set_rucss_option() {
		return true;
	}

	public function return_prewarmup_stats( ) {
		return [
			'allow_optimization' => true,
		];
	}
}
